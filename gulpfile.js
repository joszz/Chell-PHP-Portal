// include plug-ins
var gulp = require('gulp');
var package = require('./package.json');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var del = require('del');
var cssnano = require('gulp-cssnano');
var header = require('gulp-header');
var replace = require('gulp-string-replace');
var rename = require('gulp-rename');

var banner = {
    main:
        '/*!' +
        ' <%= package.name %> v<%= package.version %>' +
        ' | (c) ' + new Date().getFullYear() + ' <%= package.author.name %>' +
        ' | <%= package.license %> License' +
        ' */\n'
};

var config = {
    styles_src: {
        output_path: 'css/default/',
        sass: ['css/default/default.scss', 'css/default/exception.scss', 'css/default/install.scss'],
        bundle: [
            'vendor/fancybox/jquery.fancybox.css',
            'vendor/waves/waves.css',
            'vendor/bootstrap-select/css/bootstrap-select.css',
            'vendor/bootstrap-toggle/css/bootstrap-toggle.css',
            'vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.css',
            'vendor/bootstrap-toggle/css/bootstrap-toggle.css',
            'css/default/default.css'
        ]
    },
    js_src: {
        dashboard: ['js/dashboard-blocks/*.js', 'js/dashboard.js'],
        general: [
            'vendor/jquery/jquery.js',
            'vendor/fancybox/jquery.fancybox.js',
            'vendor/bootstrap-select/js/bootstrap-select.js',
            'vendor/bootstrap-toggle/js/bootstrap-toggle.js',
            'vendor/jquery-fullscreen-plugin/jquery.fullscreen.js',
            'vendor/chartist/dist/chartist.js',
            'vendor/chartist-plugin-legend/chartist-plugin-legend.js',
            'vendor/spark-md5/spark-md5.js',
            'vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.js',
            'vendor/bootstrap-tabcollapse/bootstrap-tabcollapse.js',
            'vendor/jquery.vibrate/jquery.vibrate.js',
            'vendor/tinytimer/jquery.tinytimer.js',
            'vendor/jquery.isloading/jquery.isloading.js',
            'vendor/waves/waves.js',
            'vendor/bootstrap-sass/assets/javascripts/bootstrap.js',
            "js/toggle-passwords.js",
            'js/general.js'
        ],
        login: ['js/login.js', 'vendor/webauthn/webauthnauthenticate.js'],
        worker: ['js/worker.js'],
        speedtest_worker: ['vendor/speedtest/speedtest_worker.js'],
        settings: ['js/settings.js', 'vendor/webauthn/webauthnregister.js'],
    }
};

function compile_sass(done) {
    config.styles_src.sass.forEach(css => {
        var css_file = css.replace('.scss', '');
        del([css_file + '.css', css_file + '.min.css']);
    })

    return gulp.src(config.styles_src.sass)
        .pipe(sass())
        .pipe(gulp.dest(config.styles_src.output_path))
        .pipe(rename({ suffix: '.min' }))
        .pipe(
            cssnano({
                discardComments: {
                    removeAll: true
                }
            })
        )
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.styles_src.output_path));
}

function bundle_css() {
    del([config.styles_src.output_path + 'bundle.css', config.styles_src.output_path + 'bundle.min.css']);

    return gulp.src(config.styles_src.bundle)
        .pipe(concat('bundle.css'))
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.styles_src.output_path))
        .pipe(rename({ suffix: '.min' }))
        .pipe(
            cssnano({
                discardComments: {
                    removeAll: true
                }
            })
        )
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.styles_src.output_path));
}

function scripts(done) {
    for (js in config.js_src) {
        del(['js/' + js + '.min.js']);

        result = gulp.src(config.js_src[js])
            .pipe(replace('"use strict";', '', {
                logs: { enabled: false }
            }))
            .pipe(uglify())
            .pipe(concat(js + '.min.js', { newLine: ';' }))
            .pipe(header(banner.main + '"use strict";', { package: package }))
            .pipe(gulp.dest('js/'));
    }

    return done();
}

function watch() {
    gulp.watch('css/default/**/*.scss', gulp.series(['sass']));
    gulp.watch('js/**/*.js', gulp.series(['js']));
}

const styles = gulp.series(compile_sass, bundle_css);

exports.scripts = scripts;
exports.styles = styles;
exports.watch = watch;
exports.default = gulp.parallel(scripts, styles);
