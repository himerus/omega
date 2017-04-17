'use strict';
const _ = require('lodash');
const fs = require('fs');
const sassJson = require('gulp-sass-json');
const YAML = require('yamljs');
const sassGlob = require('gulp-sass-glob');
const sourcemaps = require('gulp-sourcemaps');
const sass = require('gulp-sass');
const stylelint = require('gulp-stylelint');
const postcss = require('gulp-postcss');
const cached = require('gulp-cached');
const autoprefixer = require('autoprefixer');
const plumber = require('gulp-plumber');
const notify = require('gulp-notify');
const flatten = require('gulp-flatten');
const gulpif = require('gulp-if');
const join = require('path').join;
const del = require('del');

module.exports = (gulp, config, tasks) => {
    function cssCompile(done, errorShouldExit) {
        gulp.src(config.css.src)
            .pipe(sassGlob())
            .pipe(plumber({
                errorHandler(error) {
                    notify.onError({
                        title: 'CSS <%= error.name %> - Line <%= error.line %>',
                        message: '<%= error.message %>',
                    })(error);
                    if (errorShouldExit) process.exit(1);
                    this.emit('end');
                },
            }))
            .pipe(sourcemaps.init({
                debug: config.debug,
            }))
            .pipe(sass({
                outputStyle: config.css.outputStyle,
                sourceComments: config.css.sourceComments,
                includePaths: config.css.includePaths,
            }).on('error', sass.logError))
            .pipe(postcss(
                [
                    autoprefixer({
                        browsers: config.css.autoPrefixerBrowsers,
                    }),
                ]
            ))
            .pipe(sourcemaps.write((config.css.sourceMapEmbed) ? null : './'))
            .pipe(gulpif(config.css.flattenDestOutput, flatten()))
            .pipe(gulp.dest(config.css.dest))
            .on('end', () => {
            done();
    });
    }

    cssCompile.description = 'Compile Scss to CSS using Libsass with Autoprefixer and SourceMaps';

    gulp.task('css', done => cssCompile(done, true));

    gulp.task('clean:css', (done) => {
        del([
            join(config.css.dest, '*.{css,css.map}'),
        ], { force: true }).then(() => {
        done();
});
});

    // turns scss files full of variables into json files that PL can iterate on
    function scssToJson(done) {
        config.css.scssToJson.forEach((pair) => {
            const scssVarList = _.filter(fs.readFileSync(pair.src, 'utf8').split('\n'), item => _.startsWith(item, pair.lineStartsWith));
            console.log(' -- JSON: Updating ' + pair.dest + ' with latest values from ' + pair.src + '...');
            // console.log(scssVarList, item.src);
            let varsAndValues = _.map(scssVarList, (item) => {
                // assuming `item` is `$color-gray: hsl(0, 0%, 50%); // main gray color`
                const x = item.split(':');
                const y = x[1].split(';');
                return {
                    name: x[0].trim(), // i.e. $color-gray
                    value: y[0].replace(/!.*/, '').trim(), // i.e. hsl(0, 0%, 50%) after removing `!default`
                    comment: y[1].replace('//', '').trim(), // any inline comment coming after, i.e. `// main gray color`
                };
            });

            if (!pair.allowVarValues) {
                varsAndValues = _.filter(varsAndValues, item => !_.startsWith(item.value, '$'));
            }

            fs.writeFileSync(pair.dest, JSON.stringify({
                items: varsAndValues,
                meta: {
                    description: `To add to these items, use Sass variables that start with <code>${pair.lineStartsWith}</code> in <code>${pair.src}</code>`,
                },
            }, null, '  '));
        });
        done();
    }

    if (config.css.scssToJson) {
        gulp.task('css:scss-to-json', scssToJson);
        gulp.task('watch:css:scss-to-json', () => {
            const files = config.css.scssToJson.map(file => file.src);
            gulp.watch(files, scssToJson);
        });
        tasks.watch.push('watch:css:scss-to-json');
    }

    // Turns SCSS variable files into yaml files.
    function scssToYaml(done) {
        config.css.scssToYaml.forEach((pair) => {
            const scssVarList = _.filter(fs.readFileSync(pair.src, 'utf8').split('\n'), item => _.startsWith(item, pair.lineStartsWith));
            console.log(' -- YAML: Updating ' + pair.dest + ' with latest values from ' + pair.src + '...');
            //console.log('');
            //console.log(scssVarList);
            //console.log('');
            let yml = {};
            let varsAndValues = _.map(scssVarList, (item) => {
                // assuming `item` is `$color-gray: hsl(0, 0%, 50%); // main gray color`
                const x = item.split(':');
                const y = x[1].split(';');
                let cleanName = x[0].trim().replace('$', ''); // Remove the $ sign.
                let rule = {
                    name: x[0].trim(), // i.e. $color-gray
                    value: y[0].replace(/!.*/, '').trim(), // i.e. hsl(0, 0%, 50%) after removing `!default`
                    comment: y[1].replace('//', '').trim(), // any inline comment coming after, i.e. `// main gray color`
                };

                yml[cleanName] = rule;
            });

            if (!pair.allowVarValues) {
                yml = _.filter(yml, item => !_.startsWith(item.value, '$'));
            }

            fs.writeFileSync(pair.dest, YAML.stringify(yml, 10, 2));
        });
        done();
    }

    if (config.css.scssToYaml) {
        gulp.task('css:scss-to-yaml', scssToYaml);
        gulp.task('watch:css:scss-to-yaml', () => {
            const files = config.css.scssToYaml.map(file => file.src);
            gulp.watch(files, scssToYaml);
        });
        tasks.watch.push('watch:css:scss-to-yaml');
    }

    function validateCss(errorShouldExit) {
        return gulp.src(config.css.src)
            .pipe(cached('validate:css'))
            .pipe(stylelint({
                failAfterError: errorShouldExit,
                reporters: [
                    { formatter: 'string', console: true },
                ],
            }));
    }

    function validateCssWithNoExit() {
        return validateCss(false);
    }

    validateCss.description = 'Lint Scss files';

    gulp.task('validate:css', () => validateCss(true));

    function watchCss() {
        const watchTasks = [cssCompile];
        if (config.css.lint.enabled) {
            watchTasks.push(validateCssWithNoExit);
        }
        const src = config.css.extraWatches
            ? [].concat(config.css.src, config.css.extraWatches)
            : config.css.src;
        return gulp.watch(src, gulp.parallel(watchTasks));
    }

    watchCss.description = 'Watch Scss';

    gulp.task('watch:css', watchCss);

    tasks.watch.push('watch:css');

    tasks.compile.push('css');

    if (config.css.scssToJson) {
        tasks.compile.push('css:scss-to-json');
    }

    if (config.css.scssToYaml) {
        tasks.compile.push('css:scss-to-yaml');
    }

    if (config.css.lint.enabled) {
        tasks.validate.push('validate:css');
    }

    tasks.clean.push('clean:css');
};