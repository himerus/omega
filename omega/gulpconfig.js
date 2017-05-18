module.exports = {
  css: {
    enabled: true,
    debug: false,
    src: [
      'layouts/**/*.scss',
      'fonts/**/*.scss',
      'style/scss/**/*.scss',
    ],
    dest: 'public/css/',
    flattenDestOutput: true,
    lint: {
      enabled: true,
      failOnError: true,
      // in addition to linting `css.src`, this is added.
      extraSrc: [],
    },
    scssToYaml: [
      {
        src: 'style/scss/_defaults.scss',
        dest: 'style/scss/_defaults.yml',
        lineStartsWith: '$',
        allowVarValues: true,
      }
    ],
    // additional debugging info in comment of the output CSS - only use when necessary
    sourceComments: false,
    sourceMapEmbed: true, // Actually true is off, false is on. lol.
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
    enabled: false,
    themeFile: 'patternlab.info.yml',
    // when these files change
    watch: [
      'templates/**',
      '*.theme',
    ],
    // run this command
    command: 'drush cache-rebuild',
    // in this directory
    dir: './',
  },
};
