﻿const gulp = require('gulp');
const package = require('./package.json');
const sass = require('gulp-sass');
const uglify = require('gulp-uglify');
const del = require('del');
const cssnano = require('gulp-cssnano');
const header = require('gulp-header');
const rename = require('gulp-rename');

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
            'css/install.scss',
            'css/dashboard.scss',
            'css/settings.scss',
            'css/dashboard/*.scss',
        ],
        css: [
            'node_modules/waves/dist/waves.css',
            'node_modules/bootstrap-select/dist/css/bootstrap-select.css',
            'node_modules/bootstrap-toggle/css/bootstrap-toggle.css',
            'node_modules/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.css',
            'node_modules/bootstrap-toggle/css/bootstrap-toggle.css',
            'node_modules/@fancyapps/fancybox/dist/jquery.fancybox.css',
            'node_modules/prismjs/plugins/line-numbers/prism-line-numbers.css',
            'node_modules/color-calendar/dist/css/theme-basic.css',
        ]
    },
    scripts: {
        output_path: 'js/',
        src: [
            'node_modules/jquery/dist/jquery.js',
            'node_modules/@fancyapps/fancybox/dist/jquery.fancybox.js',
            'node_modules/bootstrap-select/dist/js/bootstrap-select.js',
            'node_modules/bootstrap-toggle/js/bootstrap-toggle.js',
            'node_modules/jquery-fullscreen-plugin/jquery.fullscreen.js',
            'node_modules/spark-md5/spark-md5.js',
            'node_modules/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.js',
            'node_modules/jquery.vibrate/build/jquery/jquery.vibrate.js',
            'node_modules/jquery-tinytimer/jquery.tinytimer.js',
            'node_modules/jquery.isloading/jquery.isloading.js',
            'node_modules/waves/dist/waves.js',
            'node_modules/bootstrap-sass/assets/javascripts/bootstrap.js',
            'node_modules/webauthn/src/webauthnauthenticate.js',
            'node_modules/speedtest/speedtest_worker.js',
            'node_modules/jquery/dist/jquery.js',
            'node_modules/prismjs/prism.js',
            'node_modules/prismjs/components/prism-markup-templating.js',
            'node_modules/prismjs/components/prism-php.js',
            'node_modules/prismjs/components/prism-php-extras.js',
            'node_modules/prismjs/plugins/line-numbers/prism-line-numbers.js',
            'node_modules/webauthn/src/webauthnregister.js',
            'node_modules/@duosecurity/duo_web/js/Duo-Web-v2.js',
            'node_modules/chart.js/dist/chart.js',
            'node_modules/luxon/build/global/luxon.js',
            'node_modules/chartjs-adapter-luxon/dist/chartjs-adapter-luxon.js',
            'node_modules/chartjs-plugin-streaming/dist/chartjs-plugin-streaming.js',
            'node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.js',
            'node_modules/color-calendar/dist/bundle.js',
            'js/dashboard-blocks/*.js',
            'js/dashboard.js',
            'js/toggle-passwords.js',
            'js/general.js',
            'js/login.js',
            'js/settings.js',
            'js/worker.js',
            'js/exception.js',
            'js/redirect_to_base.js'
        ]
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

function build_sass() {
    return gulp.src(config.styles.sass)
        .pipe(sass())
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.styles.output_path))
        .pipe(rename({ suffix: '.min' }))
        .pipe(
            cssnano({
                discardComments: {
                    removeAll: true
                },
                zindex: false
            })
        )
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.styles.output_path));
}

function build_styles() {
    return gulp.src(config.styles.css)
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.styles.output_path))
        .pipe(rename({ suffix: '.min' }))
        .pipe(
            cssnano({
                reduceIdents: {
                    keyframes: false
                },
                discardComments: {
                    removeAll: true
                },
                zindex: false
            })
        )
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.styles.output_path));
}

function build_scripts(done) {
    return gulp.src(config.scripts.src)
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.scripts.output_path))
        .pipe(rename({ suffix: '.min' }))
        .pipe(uglify())
        .pipe(header(banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.scripts.output_path));
}

function watch() {
    gulp.watch('css/**/*.scss', gulp.series(['styles']));
    gulp.watch('js/**/*.js', gulp.series(['scripts']));
}

exports.scripts = gulp.series((done) => clean(done, 'js'), build_scripts);
exports.styles = gulp.series((done) => clean(done, 'css'), build_sass, build_styles);
exports.default = gulp.parallel(exports.scripts, exports.styles);
exports.watch = watch;