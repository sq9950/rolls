var gulp = require('gulp');
var postcss = require('gulp-postcss');
var cssnano = require('cssnano');
var sourcemaps = require('gulp-sourcemaps');

var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var concat = require('gulp-concat');
var notify = require('gulp-notify');

gulp.task('styles', function() {
    var styles = ['./node_modules/normalize.css/normalize.css', './src/styles/index.css', './src/styles/print.css', './src/styles/progressbar.css'];
    var processors = [
        cssnano()
    ];
    return gulp.src(styles)
        .pipe(sourcemaps.init())
        .pipe(concat('bound.css'))
        .pipe(gulp.dest('dist/styles'))
        .pipe(postcss(processors))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('dist/styles'))
        .pipe(notify({
            message: "Generated file: <%= file.relative %> @ <%= options.date %>",
            templateOptions: {
                date: new Date()
            }
        }));
});

gulp.task('scripts', function() {
    var scripts = ['./node_modules/jquery/dist/jquery.js',
        './node_modules/moment/moment.js',
        './node_modules/moment/locale/zh-cn.js',
        './node_modules/chart.js/dist/Chart.js',
        './node_modules/progressbar.js/dist/progressbar.js',
        './src/scripts/mrChart.js',
        './src/scripts/index.js'
    ];
    return gulp.src(scripts)
        .pipe(sourcemaps.init())
        .pipe(concat('bound.js'))
        .pipe(gulp.dest('dist/scripts'))
        .pipe(uglify())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('dist/scripts'))
        .pipe(notify({
            message: "Generated file: <%= file.relative %> @ <%= options.date %>",
            templateOptions: {
                date: new Date()
            }
        }));
});

gulp.task('watch', function() {
    var watch_styles = gulp.watch('./src/styles/*.css', ['styles']);
    watch_styles.on('change', function(event) {
        console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
    });

    var watch_scripts = gulp.watch('./src/scripts/*.js', ['scripts']);
    watch_scripts.on('change', function(event) {
        console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
    });
});

gulp.task('build', ['styles', 'scripts']);
gulp.task('watch', ['build', 'watch']);
gulp.task('default', ['build']);
