'use strict';
const gulp = require('gulp');
// `rc` allows all config options to be overridden with CLI flags: https://www.npmjs.com/package/rc
const config = require('rc')('./lib/index.js', require('./gulpconfig.js'));
const core = require('./lib/index');

const tasks = {
  compile: [],
  watch: [],
  validate: [],
  clean: [],
  default: [],
};

core(gulp, config, tasks);

gulp.task('clean', gulp.parallel(tasks.clean));
gulp.task('compile', gulp.series(
  'clean',
  gulp.series(tasks.compile)
));
gulp.task('validate', gulp.parallel(tasks.validate));
gulp.task('watch', gulp.parallel(tasks.watch));
tasks.default.push('watch');
gulp.task('default', gulp.series(
  'compile',
  gulp.parallel(tasks.default)
));
