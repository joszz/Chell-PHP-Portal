var gulp = require('gulp');
var package = require('./package.json');
var sass = require('gulp-sass');
var uglify = require('gulp-uglify');
var del = require('del');
var cssnano = require('gulp-cssnano');
var header = require('gulp-header');
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
            'css/install.scss',
            'css/dashboard.scss',
            'css/settings.scss',
            'css/dashboard/gallery.scss',
            'css/dashboard/hyperv-admin.scss',
            'css/dashboard/motion.scss',
            'css/dashboard/opcache.scss',
            'css/dashboard/rcpu.scss',
            'css/dashboard/roborock.scss',
            'css/dashboard/sickrage.scss',
            'css/dashboard/speedtest.scss',
            'css/dashboard/phpsysinfo.scss',
            'css/dashboard/torrents.scss',
            'css/dashboard/verisure.scss',
            'css/dashboard/youless.scss',
        ],
        css: [
            'node_modules/waves/dist/waves.css',
            'node_modules/bootstrap-select/dist/css/bootstrap-select.css',
            'node_modules/bootstrap-toggle/css/bootstrap-toggle.css',
            'node_modules/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.css',
            'node_modules/bootstrap-toggle/css/bootstrap-toggle.css',
            'node_modules/@fancyapps/fancybox/dist/jquery.fancybox.css',
            'node_modules/prismjs/plugins/line-numbers/prism-line-numbers.css',
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
            'node_modules/chartist/dist/chartist.js',
            'node_modules/chartist-plugin-legend/chartist-plugin-legend.js',
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
            'js/dashboard-blocks/*.js',
            'js/dashboard.js',
            'js/toggle-passwords.js',
            'js/general.js',
            'js/login.js',
            'js/settings.js',
            'js/worker.js',
            'js/exception.js',
            'js/duo.js'
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