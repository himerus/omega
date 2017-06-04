module.exports = {
  css: {
    enabled: true,
    debug: false,
    src: [
      'layouts/**/*.scss',
      'fonts/**/*.scss',
      'style/scss/**/*.scss',
      // LOL, don't want to turn this on. You'd think if SCSS passes, so would the CSS.
      //'public/css/**/*.css',
    ],
    dest: 'public/css/',
    // Keep all compiled CSS in a single directory rather than in subdirectories that match the SCSS locations
    flattenDestOutput: true,
    lint: {
      enabled: true,
      failOnError: true,
      // in addition to linting `css.src`, this is added.
      extraSrc: [

      ],
    },
    // additional debugging info in comment of the output CSS - only use when necessary
    sourceComments: false,
    // true: Create sourcemaps, false: don't.
    sourceMapEmbed: false,
    // tell the compiler whether you want 'expanded' or 'compressed' output code
    outputStyle: 'expanded',
    // https://github.com/ai/browserslist#queries
    autoPrefixerBrowsers: [
      'last 2 versions',
      'IE >= 10',
    ],
    includePaths: [
      './node_modules',
      'style/scss',
      'style/scss/grids',
    ],
    // http://sassdoc.com
    sassdoc: {
      enabled: false,
      dest: 'public/sassdoc',
      verbose: false,
      basePath: '',
      exclude: [],
      theme: 'default',
      // http://sassdoc.com/customising-the-view/#sort
      sort: [
        'file',
        'group',
        'line>',
      ],
    },
    csscss: {
      enabled: false, // @todo: Make gulp-csscss work properly.
    },
    scssToYaml: [
      {
        src: 'style/scss/_defaults.scss',
        dest: 'style/scss/_defaults.yml',
        lineStartsWith: '$',
        allowVarValues: true,
      }
    ],
  },
  js: {
    enabled: true,
    src: [
      'js/**/*.js',
    ],
    dest: 'public/js',
    destName: 'script.min.js',
    sourceMapEmbed: false,
    uglify: false,
    babel: false,
    // Will bundle all bower JS dependencies (not devDeps) and create a `bower_components.min.js` file in `js.dest`.
    bundleBower: false,
    bundleBowerExclusions: [],
    bowerBasePath: './',
    eslint: {
      enabled: true,
      src: [
        //'js/**/*.js', // @todo: Yeah... JS
        'gulpfile.js',
        'lib/**/*.js',
      ],
    },
  },
  browserSync: {
    enabled: false,
    port: 3050,
    watchFiles: [],
    // enable when full CMS is set up
    // domain: 'mysite.dev',
    baseDir: './',
    startPath: 'pattern-lab/public/',
    openBrowserAtStart: false,
    // requires above to be true; allows non-default browser to open
    browser: [
      'Google Chrome',
    ],
    // Tunnel the Browsersync server through a random Public URL
    // -> http://randomstring23232.localtunnel.me
    tunnel: false,
    reloadDelay: 50,
    reloadDebounce: 750,
    rewriteRules: [],
  },
  drupal: {
    // todo: Find a way to handle using the drush alias
    enabled: false,
    themeFile: 'omega.info.yml',
    // when these files change
    watch: [
      '*.theme',
      '*.html.twig',
    ],
    // run this command
    command: 'drush cache-rebuild',
    // in this directory
    dir: './',
  },
};
