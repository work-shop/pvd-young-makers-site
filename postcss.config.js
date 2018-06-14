const atImport = require("postcss-import");
const tailwindcss = require("tailwindcss");
const cssnext = require("postcss-cssnext");
const cssnano = require("cssnano");

module.exports = {
  plugins: [
    atImport(),
    tailwindcss("./tailwind-config.js"),
    cssnext({
      browsers: ["> 5%", "last 2 versions", "Firefox ESR"],
      warnForDuplicates: false
    }),
    cssnano()
  ]
};
