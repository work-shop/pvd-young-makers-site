var mapModule = require( '@work-shop/map-module' )
var pvdymTileStyle = require( './tile-style.json' )

module.exports = makeSlippyMap;

function makeSlippyMap () {

  var pvdymOrange = '#e36f1e'
  var ProvidenceLatLng = { lat: 41.824, lng: -71.4128 }

  return mapModule( {
    selector: ".ws-map",
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: false,
    styles: pvdymTileStyle,
    center: ProvidenceLatLng,
    zoom: 14,
    marker: {
      icon: {
        fillColor: pvdymOrange,
      },
      popup: {
        pointer: "8px",
      }
    },
  } )

}
