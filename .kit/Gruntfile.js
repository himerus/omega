module.exports = function(grunt) {
    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        compass: {
            OMEGA_SUBTHEME: {
                options: {
                    sourcemap: true,
                    config: 'config.rb',
                    basePath: './',
                    bundleExec: true
                }
            }
        },

        focus: {
            watch: {
                // grunt-focus is the 'only' way I found to watch and compile
                // multiple SCSS dirs (themes) with a single command inside the
                // base theme.
                include: ['OMEGA_SUBTHEME']
            }
        },

        watch: {
            OMEGA_SUBTHEME: {
                files: './style/scss/**/*.scss',
                // Each theme will need to be added here
                tasks: ['compass:OMEGA_SUBTHEME']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-compass');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-focus');

    // Default task watches everything
    grunt.registerTask('default', ['focus:watch']);
};
