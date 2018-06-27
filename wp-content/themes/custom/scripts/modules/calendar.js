import $ from "jquery";
import "babel-polyfill";
import fullCalendar from "fullcalendar";

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
      year: parseInt(searchParams.get("year"), 10),
      month: parseInt(searchParams.get("month"), 10),
      type: parseInt(searchParams.get("type"), 10),
      location: parseInt(searchParams.get("location"), 10),
      tool: parseInt(searchParams.get("tool"), 10)
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
      defaultDate: selectedDate,
      events: this.getEvents.bind(this),
      eventRender: this.renderEvent.bind(this),
      displayEventTime: false,
      height: "auto",
      header: false,
      dayClick: this.handleDayClick.bind(this)
    });

    this.isInstantiated = true;
  }

  async getEvents(start, end, timezone, callback) {
    let jsonUrl = "/wp-json/wp/v2/events";

    console.log(this.params);

    if (this.params.type && this.params.type !== -1) {
      jsonUrl += `?event-types=${this.params.type}`;
    }

    if (this.params.location) {
    }

    if (this.params.tool) {
    }

    const response = await fetch(jsonUrl);
    const events = await response.json();
    const dateFormat = "DD/MM/YYYY hh:mm a";
    const formattedEvents = [];

    for (const event of events) {
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

function initCalendar() {
  // async function getCategoryIdFromSlug(slug) {
  //   const url = `/wp-json/wp/v2/event_category?slug=${slug}`;
  //   const response = await fetch(url);
  //   const data = await response.json();
  //   const id = data[0].id;
  //   return id;
  // }
  // async function getActiveTeamIdFromSlug(slug) {
  //   const url = `/wp-json/wp/v2/active_team?slug=${slug}`;
  //   const response = await fetch(url);
  //   const data = await response.json();
  //   const id = data[0].id;
  //   return id;
  // }
  // async function filterEventsByActiveTeam(events) {
  //   let filteredEvents = events;
  //   const activeTeamMatches = pathname.match(/\/active-team\/([\w-]+)$/);
  //   if (activeTeamMatches) {
  //     const teamSlug = activeTeamMatches[1];
  //     const activeTeamId = await getActiveTeamIdFromSlug(teamSlug);
  //     filteredEvents = [];
  //     for (const event of events) {
  //       if (event.acf.active_team_association === activeTeamId) {
  //         filteredEvents.push(event);
  //       }
  //     }
  //   }
  //   return filteredEvents;
  // }
}
