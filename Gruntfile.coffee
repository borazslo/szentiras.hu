paths =
    scripts: ['app/assets/js/**/*.js']
    coffee: ['app/assets/js/**/*.coffee']
    styleSheets: ['app/assets/css/**/*.css']
    images: 'app/assets/img/*'

module.exports = (grunt) ->
  grunt.initConfig
    requirejs:
      compile:
        options:
          baseUrl: 'public/js'
          name: 'app_modules'
          out: 'public/js/app_bundle.js'
          paths: {
            jquery: "empty:"
          }
    clean: ["public/js", "public/img", "public/css"]
    coffee:
      compile:
        expand: true
        cwd: 'app/assets/js'
        src: ['**/*.coffee']
        dest: 'public/js'
        ext: '.js'
    copy:
      main:
        files: [
          {
            expand: true
            cwd: 'app/assets/css'
            src: ['**/*.css']
            dest: 'public/css'
          }
          {
            expand: true
            cwd: 'app/assets/fonts'
            src: ['**/*.*']
            dest: 'public/fonts'
          }
          {
            expand: true
            flatten: true
            src: ['bower_components/bootstrap/dist/css/bootstrap.min.css']
            dest: 'public/css'
          }
          {
            expand: true
            flatten: true
            src: [
              'bower_components/bootstrap/dist/js/bootstrap.min.js'
              'bower_components/requirejs/require.js'
              'bower_components/jquery/dist/jquery.min.js'
              'bower_components/typeahead.js/dist/typeahead.jquery.min.js'
            ]
            dest: 'public/js/lib'
          }
          {
            expand: true
            cwd: 'app/assets/img'
            src: [ '*' ]
            dest: 'public/img'
          }
        ]

  grunt.loadNpmTasks task for task in [
    'grunt-contrib-uglify'
    'grunt-contrib-requirejs'
    'grunt-contrib-clean'
    'grunt-contrib-coffee'
    'grunt-contrib-copy'
  ]

  grunt.registerTask 'default', ['clean', 'copy', 'coffee', 'requirejs']
