import "babel-polyfill";
import "fullcalendar";
import Tooltip from "tooltip.js";
import $ from "jquery";

export class Calendar {
  constructor() {
    this.isInstantiated = false;
    this.$container = $("#fullcalendar");
    this.$filter = $(".calendar-filter");
    this.$title = $("#calendar-title").find("h2");
    this.$previousMonthLink = $("#calendar-previous-month-link");
    this.$nextMonthLink = $("#calendar-next-month-link");

    this.update();
    this.handleFiltering();
    this.render();
    this.handlePagination();
  }

  handlePagination() {
    this.$previousMonthLink.on("click", event => {
      event.preventDefault();

      const selectedDate = this.$container.fullCalendar("getDate");
      const selectedDateMinus1Month = selectedDate.clone().subtract(1, "month");
      const newMonth = selectedDateMinus1Month.format("MM");
      const newYear = selectedDateMinus1Month.format("YYYY");

      this.params.month = newMonth;
      this.params.year = newYear;

      this.updateUrl();
    });

    this.$nextMonthLink.on("click", event => {
      event.preventDefault();

      const selectedDate = this.$container.fullCalendar("getDate");
      const selectedDatePlus1Month = selectedDate.clone().add(1, "month");
      const newMonth = selectedDatePlus1Month.format("MM");
      const newYear = selectedDatePlus1Month.format("YYYY");

      this.params.month = newMonth;
      this.params.year = newYear;

      this.updateUrl();
    });
  }

  handleFiltering() {
    // Select proper option based on URL params
    this.$filter.each((i, element) => {
      const $element = $(element);
      const param = $element.data("param");
      const paramValue = this.params[param];

      if (paramValue) {
        $element.val(this.params[param]);
      }
    });

    // Update URL params on select option change
    this.$filter.change(() => this.updateUrl());
  }

  updateUrl() {
    const params = [];

    if (this.params.month && this.params.year) {
      params.push(`month=${this.params.month}`);
      params.push(`year=${this.params.year}`);
    }

    this.$filter.each((i, element) => {
      const $element = $(element);
      const value = $element.val();

      if (!value == "") {
        params.push(`${$element.data("param")}=${encodeURIComponent(value)}`);
      }
    });

    const locationHrefWithParams = `${this.locationHref}?${params.join("&")}`;
    history.replaceState({}, "", locationHrefWithParams);
    this.update();
  }

  update() {
    this.locationUrl = new URL(window.location.href);
    this.locationHref = this.locationUrl.origin + this.locationUrl.pathname;

    const searchParams = new URLSearchParams(this.locationUrl.search);
    const currentDate = $.fullCalendar.moment();
    this.params = {
      year: searchParams.get("year") || currentDate.format("YYYY"),
      month: searchParams.get("month") || currentDate.format("MM"),
      eventType: parseInt(searchParams.get("eventType"), 10),
      location: parseInt(searchParams.get("location"), 10),
      tool: parseInt(searchParams.get("tool"), 10)
    };

    this.updateCalendarTitle();

    if (this.isInstantiated) {
      this.$container.fullCalendar("gotoDate", this.getSelectedDate());
      this.$container.fullCalendar("refetchEvents");
    }
  }

  getSelectedDate() {
    return $.fullCalendar.moment(`${this.params.year}-${this.params.month}`);
  }

  updateCalendarTitle() {
    const selectedDate = this.isInstantiated
      ? this.getSelectedDate()
      : $.fullCalendar.moment();
    const formattedSelectedDate = selectedDate.format("MMMM YYYY");
    this.$title.text(`Calendar for ${formattedSelectedDate}`);
  }

  render() {
    let selectedDate;
    if (this.params.year && this.params.month) {
      selectedDate = $.fullCalendar.moment(
        `${this.params.year}-${this.params.month}`
      );
    } else {
      selectedDate = $.fullCalendar.moment();
    }

    this.$container.fullCalendar({
      defaultView: this.getResponsiveViewName(),
      defaultDate: selectedDate,
      events: this.getEvents.bind(this),
      eventRender: this.renderEvent.bind(this),
      displayEventTime: false,
      height: "auto",
      header: false,
      dayClick: this.handleDayClick.bind(this),
      windowResize: view => {
        this.$container.fullCalendar(
          "changeView",
          this.getResponsiveViewName()
        );
      }
    });

    this.isInstantiated = true;
  }

  getResponsiveViewName() {
    if ($(window).width() <= 768) {
      return "listMonth";
    } else {
      return "month";
    }
  }

  async getEvents(start, end, timezone, callback) {
    let jsonUrl = `/wp-json/wp/v2/events?per_page=100&filter[meta_key]=event_start&filter[meta_value]=${
      this.params.year
    }-${this.params.month}&filter[meta_compare]=LIKE`;

    const response = await fetch(jsonUrl);
    const events = await response.json();
    let filteredEvents = events;

    if (this.params.location && this.params.location !== -1) {
      filteredEvents = this.filterByLocation(
        filteredEvents,
        this.params.location
      );
    }

    if (this.params.eventType && this.params.eventType !== -1) {
      filteredEvents = this.filterByEventType(
        filteredEvents,
        this.params.eventType
      );
    }

    if (this.params.tool && this.params.tool !== -1) {
      filteredEvents = this.filterByTool(filteredEvents, this.params.tool);
    }

    const dateFormat = "YYYY-MM-DD HH:mm";
    const formattedEvents = [];

    for (const event of filteredEvents) {
      const formattedEvent = {
        title: event.acf.event_name,
        start: $.fullCalendar.moment(event.acf.event_start, dateFormat),
        end: $.fullCalendar.moment(event.acf.event_end, dateFormat),
        location:
          event.acf.location &&
          event.acf.location[0] &&
          event.acf.location[0].post_title,
        url: event.link
      };

      formattedEvents.push(formattedEvent);
    }

    callback(formattedEvents);
  }

  filterByLocation(events, locationId) {
    const filteredEvents = events.filter(event => {
      const locations = event.acf.location;

      if (locations.length > 0) {
        return event.acf.location.some(location => {
          return location.ID === locationId;
        });
      }
    });

    return filteredEvents;
  }

  filterByEventType(events, eventTypeId) {
    const filteredEvents = events.filter(event => {
      const eventTypes = event["event-types"];

      if (eventTypes.length > 0) {
        return eventTypes.some(eventType => {
          return eventType === eventTypeId;
        });
      }
    });

    return filteredEvents;
  }

  filterByTool(events, toolId) {
    const filteredEvents = events.filter(event => {
      const tools = event.acf.tools;

      if (tools.length > 0) {
        return event.acf.tools.some(tool => {
          return tool.ID === toolId;
        });
      }
    });

    return filteredEvents;
  }

  renderEvent(event, $element, view) {
    if (event.start && event.end) {
      let eventDate =
        $.fullCalendar.moment(event.start).format("MMMM DD, YYYY, h:mma") +
        " - ";
      if (
        event.start.format("MMMM DD, YYYY") ===
        event.end.format("MMMM DD, YYYY")
      ) {
        eventDate += event.end.format("h:mma");
      } else {
        eventDate += event.end.format("MMMM DD, YYYY, h:mma");
      }

      const eventLocation = event.location;

      const tooltip = new Tooltip($element, {
        placement: "top",
        container: document.querySelector("#calendar-tooltip"),
        template: `
          <div
            class="tooltip pointer-events-none z-50 block max-w-xs lg:max-w-md border-1 border-teal-darkest bg-teal-lightest"
            role="tooltip"
          >
            <div class="tooltip-arrow"></div>

            <div class="p-3 border-b-1 border-teal-darkest bg-teal-darkest text-white font-bold text-2xl leading-tight">
              <div class="tooltip-inner"></div>
            </div>

            <div class="flex items-center px-3 py-2 small-caps border-b-1 border-teal-darkest">
              <span class="mr-2" data-pictogram="{"></span>
              <span>${eventDate}</span>
            </div>

            <div class="flex items-center px-3 py-2 small-caps">
              <span class="mr-2" data-pictogram=","></span>
              <span>${eventLocation}</span>
            </div>
          </div>
        `
      });
      tooltip.updateTitleContent(event.title);

      $element.on({
        mouseover: () => tooltip.show(),
        mouseleave: () => tooltip.hide()
      });

      const dateString = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
      const $dayTopEvent = view.el.find(
        `.fc-day-top[data-date="${dateString}"]`
      );
      $dayTopEvent.addClass("fc-event-day-top");
      $dayTopEvent.attr("data-event-url", event.url);
    }
  }

  handleDayClick(day, jsEvent, view) {
    const $target = $(jsEvent.target);
    if ($target.hasClass("fc-event-day-top")) {
      const url = $target.attr("data-event-url");
      window.location.href = url;
    }
  }
}
