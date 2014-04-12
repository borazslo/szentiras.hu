gulp = require 'gulp'
concat = require 'gulp-concat'
uglify = require 'gulp-uglify'
imagemin = require 'gulp-imagemin'
coffee = require 'gulp-coffee'
rjs = require 'requirejs'

paths =
    scripts: ['app/assets/js/**/*.js']
    coffee: ['app/assets/js/**/*.coffee']
    styleSheets: ['app/assets/css/**/*.css']
    images: 'app/assets/img/*'

gulp.task 'coffee', ->
   gulp.src(paths.coffee)
       .pipe(coffee())
       #.pipe(uglify())
       .pipe(gulp.dest('public/js'))

gulp.task 'r.js', ['coffee'], ->
  rjs.optimize
    baseUrl: 'public/js'
    name: 'app_modules'
    out: 'public/js/app_modules.min.js'
    paths: {
      jquery: "empty:"
    }
    optimize: "none"

gulp.task 'jslib', ->
  gulp.src([
      'bower_components/requirejs/require.js'
      'bower_components/jquery/dist/jquery.js'
    ])
    .pipe(uglify())
    .pipe(gulp.dest('public/js/lib'))

gulp.task 'scripts', ->
    gulp.src(paths.scripts)
        .pipe(uglify())
        .pipe(concat('all.min.js'))
        .pipe(gulp.dest('public/js'))

gulp.task 'styleSheets', ->
    gulp.src(paths.styleSheets)
        .pipe(gulp.dest('public/css'))


gulp.task 'images', ->
    gulp.src(paths.images)
        ##.pipe(imagemin({optimizationLevel: 5}))
        .pipe(gulp.dest('public/img'))

gulp.task 'watch', ->
  gulp.watch(paths.coffee, 'coffee')

gulp.task('default', ['scripts', 'r.js', 'styleSheets', 'images', 'jslib']);