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
            enabled: false,
            failOnError: true,
            // in addition to linting `css.src`, this is added.
            extraSrc: [],
        },
        // scssToJson: [
        //     {
        //         src: 'style/scss/_defaults.scss',
        //         dest: 'style/scss/_defaults.json',
        //         lineStartsWith: '$',
        //         allowVarValues: true,
        //     }
        // ],
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
    }
};
