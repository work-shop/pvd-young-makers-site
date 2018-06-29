export function livereload() {

    if ( window.location.host.indexOf('pvdyoungmakers.com') === -1 ) {
      console.log( 'livereload enabled' );
      document.write(
        '<script src="http://' +
          (location.host || "localhost").split(":")[0] +
          ':35729/livereload.js?snipver=1"></' +
          "script>"
      );
  } else {
      console.log( 'livereload disabled' );
  }
}
