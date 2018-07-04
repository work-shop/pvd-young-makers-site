import { livereload } from "./modules/livereload-client.js";
import { Calendar } from "./modules/calendar.js";
import "./modules/nav.js";
import makeSlippyMaps from "@work-shop/map-module";

livereload();

const calendar = new Calendar();

const maps = makeSlippyMaps({
  selector: ".ws-map",
  map: {
    mapTypeControl: false,
    fullscreenControl: false
  },
  marker: {
    popup: {
      pointer: "8px"
    }
  },
  render: {
    center: { lat: 41.824, lng: -71.4128 },
    zoom: 14
  }
});
