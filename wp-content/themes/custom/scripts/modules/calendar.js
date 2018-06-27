import $ from "jquery";
import "babel-polyfill";
import fullCalendar from "fullcalendar";

export function initCalendar() {
  const pathname = window.location.pathname;
  const yearMonthMatches = pathname.match(/\/(\d+)\/(\d+)$/);
  let defaultDate;
  if (yearMonthMatches) {
    const year = yearMonthMatches[1];
    const month = yearMonthMatches[2];
    defaultDate = $.fullCalendar.moment(`${year}-${month}`);
  } else {
    defaultDate = $.fullCalendar.moment();
  }

  $("#fullcalendar").fullCalendar({
    events: getEvents,
    defaultDate,
    eventRender: renderEvent,
    height: "auto",
    header: false,
    dayClick: onDayClick,
    displayEventTime: false
  });

  function renderEvent(event, element, view) {
    const dateString = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
    const $dayTopEvent = view.el.find(`.fc-day-top[data-date="${dateString}"]`);
    $dayTopEvent.addClass("fc-event-day-top");
    $dayTopEvent.attr("data-event-url", event.url);
  }

  async function getEvents(start, end, timezone, callback) {
    let url = "/wp-json/wp/v2/events";
    const categoryMatches = pathname.match(/\/event-category\/([\w-]+)/);
    if (categoryMatches) {
      const categorySlug = categoryMatches[1];
      const categoryId = await getCategoryIdFromSlug(categorySlug);
      url += `?event_category=${categoryId}`;
    }
    const response = await fetch(url);
    const data = await response.json();
    const events = await filterEventsByActiveTeam(data);
    const formattedEvents = [];
    const dateFormat = "DD/MM/YYYY hh:mm a";
    for (const event of events) {
      console.log(event);
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

  async function getCategoryIdFromSlug(slug) {
    const url = `/wp-json/wp/v2/event_category?slug=${slug}`;
    const response = await fetch(url);
    const data = await response.json();
    const id = data[0].id;
    return id;
  }

  async function getActiveTeamIdFromSlug(slug) {
    const url = `/wp-json/wp/v2/active_team?slug=${slug}`;
    const response = await fetch(url);
    const data = await response.json();
    const id = data[0].id;
    return id;
  }

  async function filterEventsByActiveTeam(events) {
    let filteredEvents = events;
    const activeTeamMatches = pathname.match(/\/active-team\/([\w-]+)$/);
    if (activeTeamMatches) {
      const teamSlug = activeTeamMatches[1];
      const activeTeamId = await getActiveTeamIdFromSlug(teamSlug);
      filteredEvents = [];
      for (const event of events) {
        if (event.acf.active_team_association === activeTeamId) {
          filteredEvents.push(event);
        }
      }
    }
    return filteredEvents;
  }

  function onDayClick(day, jsEvent, view) {
    const $target = $(jsEvent.target);
    if ($target.hasClass("fc-event-day-top")) {
      const url = $target.attr("data-event-url");
      window.location.href = url;
    }
  }
}
