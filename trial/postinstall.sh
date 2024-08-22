# Since we're not using a bundler, we need to manually copy the required
# dependnecies from node_modules
#
# NOTE: Rollup has been evaluated, but even with preserveModules it does not
# copy over the required .wasm files or .so files, and preserves the dependencies
# directory structure.
# NOTE: Work needs to be done to see about further leveraging Webpack beyond
# compiling the bundles non-ESM service worker.
rsync --exclude='*[nN]ode*' --exclude='*[wW]ebview*' --exclude='php-tags*' --exclude='*index*' node_modules/php-*/*.mjs* public
rsync --exclude='php8.0*' --exclude='php8.1*' --exclude='php8.2*' node_modules/*/*.so public
