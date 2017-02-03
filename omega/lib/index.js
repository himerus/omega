'use strict';
const _ = require('lodash');
const defaultConfig = require('./config.default.js');

module.exports = (gulp, userConfig, tasks) => {
    const config = _.merge(defaultConfig, userConfig);

    if (config.css.enabled) {
        require('./css')(gulp, config, tasks);
    }

    tasks.compile = gulp.series(tasks.compile);
};
