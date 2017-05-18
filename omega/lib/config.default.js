module.exports = {
  css: {
    enabled: true,
    debug: false,
    src: [
      'layouts/**/*.scss',
      'style/scss/**/*.scss',
    ],
    dest: 'public/css/',
    flattenDestOutput: true,
    lint: {
      enabled: false,
      failOnError: true,
      // in addition to linting `css.src`, this is added.
      extraSrc: [],
    },
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
    // http://sassdoc.com
    sassdoc: {
      enabled: false,
      dest: 'dest/sassdoc',
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
  },
};
