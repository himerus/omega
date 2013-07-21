'use strict';

module.exports = function (grunt) {

  grunt.initConfig({
    watch: {
      options: {
        livereload: true
      },
      sass: {
        files: ['sass/{,**/}*.{scss,sass}'],
        tasks: ['compass:dev']
      },
      images: {
        files: ['images/**']
      },
      css: {
        files: ['stylesheets/{,**/}*.css']
      },
      js: {
        files: ['js/{,**/}*.js', '!js/{,**/}*.js'],
        tasks: ['jshint', 'uglify:dev']
      }
    },

    compass: {
      options: {
        config: 'config.rb',
        bundleExec: true
      },
      dev: {
        options: {
          environment: 'development',
          force: true
        }
      },
      dist: {
        options: {
          environment: 'production',
          force: true
        }
      }
    },

    jshint: {
      options: {
        jshintrc: '.jshintrc'
      },
      all: ['js/{,**/}*.js', '!js/{,**/}*.min.js']
    },

    uglify: {
      dev: {
        options: {
          mangle: false,
          compress: false,
          beautify: true
        },
        files: [{
          expand: true,
          cwd: 'js',
          src: ['**/*.js', '!**/*.min.js'],
          dest: 'js',
          ext: '.min.js'
        }]
      },
      dist: {
        options: {
          mangle: true,
          compress: true
        },
        files: [{
          expand: true,
          cwd: 'js',
          src: ['**/*.js', '!**/*.min.js'],
          dest: 'js',
          ext: '.min.js'
        }]
      },
      components: {
        options: {
          mangle: false,
          compress: false
        },
        files: {
          'components/matchmedia/matchMedia.min.js': ['components/matchmedia/matchMedia.js'],
          'components/matchmedia/matchMedia.addListener.min.js':  ['components/matchmedia/matchMedia.addListener.js'],
          'components/selectivizr/selectivizr.min.js': ['components/selectivizr/selectivizr.js'],
          'components/css3mediaqueries/css3-mediaqueries.min.js': ['components/css3mediaqueries/css3-mediaqueries.js']
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  grunt.registerTask('build', [
    'uglify:dist',
    'compass:dist',
    'jshint'
  ]);

};
