// Get modules
var gulp = require('gulp');

// Task boilerplate
gulp.task('taskname', function() {

});

// The default task (called when you run `gulp` from cli)
gulp.task('default', function() {
    gulp.src('./app/assets/css/*.css')
        .pipe(gulp.dest('./public/css'));
    gulp.src('./app/assets/img/*.*')
        .pipe(gulp.dest('./public/img'));
    gulp.src('./app/assets/js/*.js')
        .pipe(gulp.dest('./public/js'));
});