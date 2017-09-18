const gulp = require('gulp');
const less = require('gulp-less');
const header = require('gulp-header');
const rename = require('gulp-rename');
const uglify = require('gulp-uglify');
const pkg = require('./package.json');
const install = require('gulp-install');

// Set the banner content
var banner = ['/*!\n',
  ' * <%= pkg.title %> - v<%= pkg.version %>\n',
  ' */\n',
  ''
].join('');

// Compile LESS files from /less into /css
gulp.task('less', function () {
  return gulp.src('assets/less/*.less')
    .pipe(less())
    .pipe(header(banner, {pkg: pkg}))
    .pipe(gulp.dest('assets/css/'));
});

// Copy vendor libraries from /node_modules into /vendor
gulp.task('copy', function () {

  gulp.src([
    'node_modules/font-awesome/scss/**/*',
  ])
    .pipe(gulp.dest('vendor/font-awesome/scss'));

  gulp.src([
    'node_modules/font-awesome/fonts/**/*',
  ])
    .pipe(gulp.dest('vendor/font-awesome/fonts'));

  gulp.src(['node_modules/jquery/dist/jquery.js', 'node_modules/jquery/dist/jquery.min.js'])
    .pipe(gulp.dest('vendor/jquery'));

  gulp.src([
    'node_modules/bootstrap/scss/**/*',
  ])
    .pipe(gulp.dest('vendor/bootstrap/scss'));

  gulp.src([
    'node_modules/bootstrap/dist/**/*',
  ])
    .pipe(gulp.dest('vendor/bootstrap'));

  gulp.src('node_modules/backbone/backbone.js')
    .pipe(gulp.dest('vendor/backbone'));

  gulp.src('node_modules/backbone/backbone-min.js')
    .pipe(rename('backbone.min.js'))
    .pipe(gulp.dest('vendor/backbone'));

  gulp.src(['node_modules/backbone.radio/build/backbone.radio.js', 'node_modules/backbone.radio/build/backbone.radio.min.js'])
    .pipe(gulp.dest('vendor/backbone-radio'));

  gulp.src(['node_modules/backbone.marionette/lib/backbone.marionette.js', 'node_modules/backbone.marionette/lib/backbone.marionette.min.js'])
    .pipe(gulp.dest('vendor/backbone-marionette'));

  gulp.src('node_modules/underscore/underscore.js')
    .pipe(gulp.dest('vendor/underscore'));

  gulp.src('node_modules/underscore/underscore-min.js')
    .pipe(rename('underscore.min.js'))
    .pipe(gulp.dest('vendor/underscore'));

  gulp.src('node_modules/lodash/dist/lodash.js')
    .pipe(gulp.dest('vendor/lodash'));

  gulp.src('node_modules/lodash/dist/lodash.min.js')
    .pipe(rename('lodash.min.js'))
    .pipe(gulp.dest('vendor/lodash'));

  gulp.src(['node_modules/requirejs/require.js'])
    .pipe(gulp.dest('vendor/requirejs'));

  gulp.src('node_modules/requirejs/require.js')
    .pipe(uglify())
    .pipe(rename('require.min.js'))
    .pipe(gulp.dest('vendor/requirejs'));

  gulp.src(['node_modules/require-css/css.js', 'node_modules/require-css/css.min.js', 'node_modules/require-css/css-builder.js',  'node_modules/require-css/normalize.js'])
    .pipe(gulp.dest('vendor/require-css'));

  gulp.src('node_modules/requirejs-text/text.js')
    .pipe(gulp.dest('vendor/require-text'));

  gulp.src('node_modules/requirejs-text/text.js')
    .pipe(uglify())
    .pipe(rename('text.min.js'))
    .pipe(gulp.dest('vendor/require-text'));

  gulp.src('node_modules/requirejs-plugins/src/*')
    .pipe(gulp.dest('vendor/require-plugins/dist'));

  gulp.src('node_modules/requirejs-plugins/lib/*')
    .pipe(gulp.dest('vendor/require-plugins/lib'));

  gulp.src(['node_modules/moment/min/moment.min.js'])
    .pipe(gulp.dest('vendor/moment'));

  gulp.src(['node_modules/material-components-web/dist/material-components-web.css', 'node_modules/material-components-web/dist/material-components-web.min.css', 'node_modules/material-components-web/dist/material-components-web.js', 'node_modules/material-components-web/dist/material-components-web.min.js'])
    .pipe(gulp.dest('vendor/material'));
});

// Run everything
gulp.task('default', ['minify-css', 'minify-js', 'views', 'copy']);

// Dev task with browserSync
gulp.task('dev', ['browserSync', 'less', 'views', 'minify-css', 'js', 'minify-js'], function () {
  gulp.watch('less/*.less', ['less']);
  gulp.watch('dist/css/*.css', ['minify-css']);
  gulp.watch('js/*.js', ['minify-js']);
});

// Watch files and compile
gulp.task('watch', function () {
  gulp.watch('less/*.less', ['less']);
  gulp.watch('js/*.js', ['js']);
  gulp.watch('pages/*.{pug,jade}', ['views']);
});

// Setup for use
gulp.task('setup', ['copy'], function () {
  // FOR FUTURE USE
});

// Install Node and Bower dependencies
gulp.task('install', function () {
  return gulp.src(['./bower.json', './package.json'])
    .pipe(install());
});
