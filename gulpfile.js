"use strict";

/** ==========================
 * Global Package Requirements
 * =========================== */
const path = require("path");
const browserify = require("browserify");
const babelify = require("babelify");
const brfs = require("brfs");
const source = require("vinyl-source-stream");
const buffer = require("vinyl-buffer");

const gulp = require("gulp");
const rename = require("gulp-rename");
const sourcemaps = require("gulp-sourcemaps");
const uglify = require("gulp-uglify");
const log = require("gulp-logger");
const livereload = require("gulp-livereload");
const postcss = require("gulp-postcss");

/** ===============================
 * Local Configuration Requirements
 * ================================ */
const pkg = require("./package.json");
const paths = pkg.paths;
const managed_files = ["js", "css"];

/** ==================================================
 * Include Paths, Watch Paths, and Compilation Targets
 * =================================================== */
const php_entrypoint = path.join(
  __dirname,
  paths.src,
  "**",
  "*.!(" + managed_files.join("|") + ")"
);
const php_exitpoint = path.join(__dirname, paths.dest);

const js_entrypoint = path.join(__dirname, paths.src, "scripts", "main.js");
const js_exitpoint = path.join(__dirname, paths.dest, "bundles");
const js_watch_files = path.join(__dirname, paths.src, "scripts", "**", "*.js");

const styles_entrypoint = path.join(__dirname, paths.src, "styles", "main.css");
const styles_exitpoint = path.join(__dirname, paths.dest, "bundles");
const styles_watch_files = path.join(
  __dirname,
  paths.src,
  "styles",
  "**",
  "*.css"
);

const admin_styles_entrypoint = path.join(
  __dirname,
  paths.src,
  "styles",
  "admin.css"
);
const admin_styles_exitpoint = path.join(__dirname, paths.dest, "bundles");
const admin_styles_watch_files = [
  path.join(__dirname, paths.src, "styles", "admin.css"),
  path.join(__dirname, paths.src, "styles", "admin", "**", "*.css")
];

const js_bundler = browserify(js_entrypoint)
  .transform(brfs)
  .transform(babelify, {
    presets: ["env"]
  });

/** =============
 * Gulp Processes
 * ============== */

/**
 * This process takes all the files that aren't controlled by
 * other gulp compilation processes and moves them to the destination unchanged.
 * if paths.src === paths.dest, then this rule does nothing.
 */
function file_bundle(development) {
  if (development && paths.src !== paths.dest) {
    return function() {
      gulp
        .src(php_entrypoint)
        .pipe(livereload())
        .pipe(gulp.dest(php_exitpoint));
    };
  } else if (paths.src !== paths.dest) {
    return function() {
      gulp.src(php_entrypoint).pipe(gulp.dest(php_exitpoint));
    };
  }
}

/**
 * This rule compiles the main.css file, producing
 * a bundle.css output.
 */
function styles_bundle(development) {
  return function() {
    gulp
      .src(styles_entrypoint)
      .pipe(sourcemaps.init())
      .pipe(postcss())
      .pipe(rename({ basename: "bundle", ext: ".css" }))
      .pipe(
        sourcemaps.write("./", {
          includeContent: false,
          sourceRoot: path.join(__dirname, paths.src)
        })
      )
      .pipe(gulp.dest(styles_exitpoint))
      .pipe(livereload());
  };
}

/**
 * This rule compiles the admin.css file, producing
 * a admin.css output.
 */
function admin_styles_bundle(development) {
  return function() {
    gulp
      .src(admin_styles_entrypoint)
      .pipe(sourcemaps.init())
      .pipe(postcss())
      .pipe(rename({ basename: "admin", ext: ".css" }))
      .pipe(
        sourcemaps.write("./", {
          includeContent: false,
          sourceRoot: path.join(__dirname, paths.src)
        })
      )
      .pipe(rename({ basename: "admin-bundle", ext: ".css" }))
      .pipe(livereload())
      .pipe(gulp.dest(admin_styles_exitpoint));
  };
}

/**
 * This rule compiles the main.js file, producing
 * a bundle.js output. It also performes source code translation
 * from ES6 to standards compliant browser JS via browserify and babelify.
 */
function js_bundle() {
  return function() {
    return (
      js_bundler
        .bundle()
        .pipe(source("bundle.js"))
        .pipe(buffer())
        .pipe(sourcemaps.init({ loadMaps: true }))
        // .pipe(uglify())
        .pipe(sourcemaps.write("./"))
        .pipe(livereload())
        .pipe(gulp.dest(js_exitpoint))
    );
  };
}

/**
 * This routine watches all files for changes, and passes those changes
 * to the livereload server, allowing for CSS and image injection, and
 * auto page refresh for other file-types.
 */
function watch() {
  livereload.listen({ start: true, quiet: false });

  gulp.watch(php_entrypoint).on("change", function(file) {
    livereload.changed(file.path);
  });
  gulp.watch(js_watch_files, ["js"]);
  gulp.watch(styles_watch_files, ["styles"]);
  gulp.watch(admin_styles_watch_files, ["admin-styles"]);
}

/** =========
 * Gulp Rules
 * ========== */

gulp.task("files", file_bundle(process.env.NODE_ENV === "development"));

gulp.task("styles", styles_bundle(process.env.NODE_ENV === "development"));

gulp.task(
  "admin-styles",
  admin_styles_bundle(process.env.NODE_ENV === "development")
);

gulp.task("js", js_bundle(process.env.NODE_ENV === "development"));

gulp.task("watch", watch);

gulp.task("build", ["admin-styles", "styles", "js"]);

gulp.task("default", ["admin-styles", "styles", "js", "watch"]);
