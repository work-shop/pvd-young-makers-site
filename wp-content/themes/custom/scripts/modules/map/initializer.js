var mapModule = require( '@work-shop/map-module' )

/* Option A - true to mockup */
// var pvdymTileStyle = require( './tile-style--option-a.json' )
/* A, with labels */
// var pvdymTileStyle = require( './tile-style--option-a2.json' )
/* A, with more labels */
// var pvdymTileStyle = require( './tile-style--option-a3.json' )
/* Option B - blue-grey land, light orange landmarks */
// var pvdymTileStyle = require( './tile-style--option-b.json' )
/* B - with labels */
// var pvdymTileStyle = require( './tile-style--option-b2.json' )
/* B - with more labels */
var pvdymTileStyle = require( './tile-style--option-b3.json' )

module.exports = makeSlippyMap;

function makeSlippyMap () {

  var pvdymOrange = '#e36f1e'
  var ProvidenceLatLng = { lat: 41.824, lng: -71.4128 }

  return mapModule({
    selector: ".ws-map",
    map: {
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false,
      styles: pvdymTileStyle,
    },
    marker: {
      icon: {
        fillColor: pvdymOrange,
      },
      popup: {
        pointer: "8px",
      }
    },
    render: {
      center: ProvidenceLatLng,
      zoom: 14
    }
  } )

}
