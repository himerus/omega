'use strict';
const _ = require('lodash');
const defaultConfig = require('./config.default');

module.exports = (gulp, userConfig, tasks) => {
  const config = _.merge(defaultConfig, userConfig);

  /* eslint-disable global-require */
  if (config.css.enabled) {
    require('./css')(gulp, config, tasks);
  }

  if (config.js.enabled) {
    require('./js')(gulp, config, tasks);
  }

  /* eslint-enable global-require */

  // Instead of `gulp.parallel`, which is what is set in Pattern Lab Starter's `gulpfile.js`, this
  // uses `gulp.series`. Needed to help with the Gulp task dependencies lost going from v3 to v4.
  // We basically need icons compiled before CSS & CSS/JS compiled before inject:pl before pl
  // compile. The order of the `require`s above is the order that compiles run in; not perfect, but
  // it works.
  // eslint-disable-next-line no-param-reassign
  tasks.compile = gulp.series(tasks.compile);
};
