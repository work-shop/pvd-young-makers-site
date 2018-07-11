const atImport = require("postcss-import");
const cssnano = require("cssnano");
const cssnext = require("postcss-cssnext");
const path = require("path");
const tailwindcss = require("tailwindcss");

module.exports = {
  plugins: [
    atImport(),
    tailwindcss(path.resolve(__dirname, "./tailwind-config.js")),
    cssnext({
      browsers: ["> 5%", "last 2 versions", "Firefox ESR"],
      warnForDuplicates: false
    }),
    cssnano()
  ]
};
