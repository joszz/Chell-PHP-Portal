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
    styles: {
        output_path: 'css/default/',
        sass: [
            'css/default/default.scss',
            'css/default/exception.scss',
            'css/default/install.scss'
        ],
        bundle: [
            'node_modules/@fancyapps/fancybox/dist/jquery.fancybox.css',
            'node_modules/waves/dist/waves.css',
            'node_modules/bootstrap-select/dist/css/bootstrap-select.css',
            'node_modules/bootstrap-toggle/css/bootstrap-toggle.css',
            'node_modules/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.css',
            'node_modules/bootstrap-toggle/css/bootstrap-toggle.css',
            'css/default/default.css'
        ]
    },
    scripts: {
        output_path: 'js/',
        src: {
            dashboard: [
                'js/dashboard-blocks/*.js',
                'js/dashboard.js'
            ],
            general: [
                'node_modules/jquery/dist/jquery.js',
                'node_modules/@fancyapps/fancybox/dist/jquery.fancybox.js',
                'node_modules/bootstrap-select/dist/js/bootstrap-select.js',
                'node_modules/bootstrap-toggle/js/bootstrap-toggle.js',
                'node_modules/jquery-fullscreen-plugin/jquery.fullscreen.js',
                'node_modules/chartist/dist/chartist.js',
                'node_modules/chartist-plugin-legend/chartist-plugin-legend.js',
                'node_modules/spark-md5/spark-md5.js',
                'node_modules/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.js',
                'node_modules/bootstrap-tabcollapse/bootstrap-tabcollapse.js',
                'node_modules/jquery.vibrate/build/jquery/jquery.vibrate.js',
                'node_modules/jquery-tinytimer/jquery.tinytimer.js',
                'node_modules/jquery.isloading/jquery.isloading.js',
                'node_modules/waves/dist/waves.js',
                'node_modules/bootstrap-sass/assets/javascripts/bootstrap.js',
                "js/toggle-passwords.js",
                'js/general.js'
            ],
            login: [
                'js/login.js',
                'node_modules/webauthn/src/webauthnauthenticate.js'
            ],
            settings: [
                'js/settings.js',
                'node_modules/webauthn/src/webauthnregister.js'
            ],
            worker: 'js/worker.js',
            speedtest_worker: 'node_modules/speedtest/speedtest_worker.js'
        }
    }
};

function compile_sass(done) {
    config.styles.sass.forEach(css => {
        var css_file = css.replace('.scss', '');
        del([css_file + '.css', css_file + '.min.css']);
    })

    return gulp.src(config.styles.sass)
        .pipe(sass())
        .pipe(gulp.dest(config.styles.output_path))
        .pipe(rename({ suffix: '.min' }))
        .pipe(
            cssnano({
                discardComments: {
                    removeAll: true
                }
            })
        )
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.styles.output_path));
}

function bundle_css() {
    del([config.styles.output_path + 'bundle.css', config.styles.output_path + 'bundle.min.css']);

    return gulp.src(config.styles.bundle)
        .pipe(concat('bundle.css'))
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.styles.output_path))
        .pipe(rename({ suffix: '.min' }))
        .pipe(
            cssnano({
                discardComments: {
                    removeAll: true
                }
            })
        )
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.styles.output_path));
}

function scripts(done) {
    for (js in config.scripts.src) {
        del([config.scripts.output_path + js + '.min.js']);

        result = gulp.src(config.scripts.src[js])
            .pipe(replace('"use strict";', '', {
                logs: { enabled: false }
            }))
            .pipe(uglify())
            .pipe(concat(js + '.min.js', { newLine: ';' }))
            .pipe(header(banner.main, { package: package }))
            .pipe(gulp.dest(config.scripts.output_path));
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
