import "babel-polyfill";
import "fullcalendar";
import $ from "jquery";

export class Calendar {
  constructor() {
    this.isInstantiated = false;
    this.$container = $("#fullcalendar");
    this.$filter = $(".calendar-filter");

    this.update();
    this.handleFiltering();
    this.render();
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
    this.$filter.change(() => {
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
    });
  }

  update() {
    this.locationUrl = new URL(window.location.href);
    this.locationHref = this.locationUrl.origin + this.locationUrl.pathname;

    const searchParams = new URLSearchParams(this.locationUrl.search);
    this.params = {
      year: searchParams.get("year"),
      month: searchParams.get("month"),
      eventType: parseInt(searchParams.get("eventType"), 10),
      location: parseInt(searchParams.get("location"), 10),
      toolType: parseInt(searchParams.get("toolType"), 10)
    };

    if (this.isInstantiated) {
      this.$container.fullCalendar("refetchEvents");
    }
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
    let jsonUrl = "/wp-json/wp/v2/events";

    if (this.params.eventType && this.params.eventType !== -1) {
      jsonUrl += `?event-types=${this.params.eventType}`;
    }

    const response = await fetch(jsonUrl);
    const events = await response.json();
    let filteredEvents = events;

    if (this.params.location && this.params.location !== -1) {
      filteredEvents = this.filterByLocation(
        filteredEvents,
        this.params.location
      );
    }

    if (this.params.toolType && this.params.toolType !== -1) {
      filteredEvents = await this.filterByToolType(
        filteredEvents,
        this.params.toolType
      );
    }

    const dateFormat = "DD/MM/YYYY hh:mm a";
    const formattedEvents = [];

    for (const event of filteredEvents) {
      const formattedEvent = {
        title: event.acf.event_name,
        start: $.fullCalendar.moment(event.acf.event_start, dateFormat),
        end: $.fullCalendar.moment(event.acf.event_end, dateFormat),
        url: event.link
      };

      formattedEvents.push(formattedEvent);
    }

    callback(formattedEvents);
  }

  filterByLocation(events, locationId) {
    const filteredEvents = events.filter(event => {
      return event.acf.location.some(location => {
        return location.ID === locationId;
      });
    });

    // const filteredEvents = filter(events, {
    //   acf: { location: [{ ID: locationId }] }
    // });

    return filteredEvents;
  }

  async filterByToolType(events, toolTypeId) {
    const jsonUrl = `/wp-json/wp/v2/tools?tool-types=${toolTypeId}`;
    const response = await fetch(jsonUrl);
    const tools = await response.json();
    const toolIds = [];

    tools.forEach(tool => toolIds.push(tool.id));

    const filteredEvents = events.filter(event => {
      return event.acf.tools.some(tool => {
        return toolIds.includes(tool.ID);
      });
    });

    return filteredEvents;
  }

  renderEvent(event, element, view) {
    const dateString = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
    const $dayTopEvent = view.el.find(`.fc-day-top[data-date="${dateString}"]`);
    $dayTopEvent.addClass("fc-event-day-top");
    $dayTopEvent.attr("data-event-url", event.url);
  }

  handleDayClick(day, jsEvent, view) {
    const $target = $(jsEvent.target);
    if ($target.hasClass("fc-event-day-top")) {
      const url = $target.attr("data-event-url");
      window.location.href = url;
    }
  }
}
