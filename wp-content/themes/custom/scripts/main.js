import { livereload } from "./modules/livereload-client.js";
import { Calendar } from "./modules/calendar.js";
import "./modules/nav.js";
import "./modules/home.js";
import makeSlippyMaps from "./modules/map/initializer.js";

livereload();

const calendar = new Calendar();
const maps = makeSlippyMaps();
