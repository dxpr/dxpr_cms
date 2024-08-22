/**
 * Firefox does not support ES modules in service workers, so we have to use
 * Webpack to bundle the service worker.
 *
 * NOTE: See if we can have Webpack replace the `postinstall` hook.
 */
const path = require("path");

module.exports = {
  mode: "production",
  module: {
    rules: [
      {
        test: /\.mjs$/,
        exclude: /node_modules/,
        use: { loader: "babel-loader" }
      }
    ]
  },
  entry: {
    "service-worker": "./public/service-worker.mjs",
  },
  output: {
    path: path.resolve(__dirname, "public"),
    filename: "[name].js"
  },
  target: "webworker"
};
