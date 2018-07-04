import $ from "jquery";

const $hero = $("#hero");
const $showMapButton = $("#show-map-button");
const $returnFromMapButton = $("#return-from-map-button");

$showMapButton.on("click", event => {
  event.preventDefault();
  $returnFromMapButton.animate({ opacity: 1 });
  $hero.animate({ opacity: 0 });
  $hero.css({ pointerEvents: "none" });
});

$returnFromMapButton.on("click", event => {
  event.preventDefault();
  $returnFromMapButton.animate({ opacity: 0 });
  $hero.animate({ opacity: 1 });
  $hero.css({ pointerEvents: "auto" });
});
