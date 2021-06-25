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
    output_path: 'dist/',
    styles: {
        output_path: 'css/',
        sass: [
            'css/default.scss',
            'css/exception.scss',
            'css/install.scss'
        ],
        bundle: [
            'node_modules/@fancyapps/fancybox/dist/jquery.fancybox.css',
            'node_modules/waves/dist/waves.css',
            'node_modules/bootstrap-select/dist/css/bootstrap-select.css',
            'node_modules/bootstrap-toggle/css/bootstrap-toggle.css',
            'node_modules/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.css',
            'node_modules/bootstrap-toggle/css/bootstrap-toggle.css',
            'dist/css/default.css'
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
                'js/toggle-passwords.js',
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
            speedtest_worker: 'node_modules/speedtest/speedtest_worker.js',
            exception: [
                'node_modules/jquery/dist/jquery.js',
                'node_modules/prismjs/prism.js',
                'node_modules/prismjs/components/prism-markup-templating.js',
                'node_modules/prismjs/components/prism-php.js',
                'node_modules/prismjs/components/prism-php-extras.js',
                'node_modules/prismjs/plugins/line-numbers/prism-line-numbers.js',
                'js/exception.js'
            ],
            duo: [
                'node_modules/@duosecurity/duo_web/js/Duo-Web-v2.js',
                'js/duo.js'
            ]
        }
    }
};

function clean(done, which) {
    if (which == 'css' || which == '*') {
        del(config.output_path + "css/*.css");
    }
    if (which == 'js' || which == '*') {
        del(config.output_path + "js/*.js");
    }

    return done();
}

function compile_sass() {
    return gulp.src(config.styles.sass)
        .pipe(sass())
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.styles.output_path))
        .pipe(rename({ suffix: '.min' }))
        .pipe(
            cssnano({
                discardComments: {
                    removeAll: true
                }
            })
        )
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.styles.output_path));
}

function bundle_css() {
    return gulp.src(config.styles.bundle)
        .pipe(gulp.dest(config.output_path + config.styles.output_path))
        .pipe(concat('bundle.css'))
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.styles.output_path))
        .pipe(rename({ suffix: '.min' }))
        .pipe(
            cssnano({
                discardComments: {
                    removeAll: true
                }
            })
        )
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.styles.output_path));
}

function scripts(done) {
    for (js in config.scripts.src) {
        gulp.src(config.scripts.src[js])
            .pipe(gulp.dest(config.output_path + config.scripts.output_path))
            .pipe(replace('"use strict";', '', {
                logs: { enabled: false }
            }))
            .pipe(uglify())
            .pipe(concat(js + '.min.js', { newLine: ';' }))
            .pipe(header(banner.main, { package: package }))
            .pipe(gulp.dest(config.output_path + config.scripts.output_path));
    }
    return done();
    return gulp.src(config.scripts.output_path + '**/*.js').pipe(gulp.dest(config.output_path + config.scripts.output_path));
}

function watch() {
    gulp.watch('css/**/*.scss', gulp.series(['sass']));
    gulp.watch('js/**/*.js', gulp.series(['js']));
}

const styles = gulp.series((done) => clean(done, 'css'), compile_sass, bundle_css);

exports.default = gulp.parallel(scripts, styles);
exports.scripts = gulp.series((done) => clean(done, 'js'), scripts);
exports.styles = styles;
exports.watch = watch;