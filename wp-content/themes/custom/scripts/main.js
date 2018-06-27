import { livereload } from "./modules/livereload-client.js";
import { Calendar } from "./modules/calendar.js";

livereload();

const calendar = new Calendar();
