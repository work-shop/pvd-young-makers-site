import $ from "jquery";

const $navToggle = $(".Nav-toggle");
const $navOverlayCloser = $(".Nav-overlayCloser");

$navOverlayCloser.on("click", () => {
  $navToggle.prop("checked", false);
});
