const gulp = require('gulp');
const package = require('./package.json');
const sass = require('gulp-sass');
const uglify = require('gulp-uglify');
const del = require('del');
const cssnano = require('gulp-cssnano');
const header = require('gulp-header');
const rename = require('gulp-rename');
const iconfont = require('gulp-iconfont');
const iconfontCss = require('gulp-iconfont-css');
const config = require("./gulpfile_config.js"); 

function clean(done, which) {
    if (which == 'css' || which == '*') {
        del(config.output_path + "css/*.css");
    }
    if (which == 'js' || which == '*') {
        del(config.output_path + "js/*.js");
    }
    if (which == 'iconfont' || which == '*') {
        del(config.output_path + "fonts/chell-icons.*");
    }
    if (which == 'robotofont' || which == '*') {
        del(config.output_path + "fonts/roboto-latin-300-normal.*");
    }

    return done();
}

function build_sass() {
    return gulp.src(config.styles.sass)
        .pipe(sass())
        .pipe(header(config.banner.main, { package: package }))
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
        .pipe(header(config.banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.styles.output_path));
}


function build_styles() {
    return gulp.src(config.styles.css)
        .pipe(header(config.banner.main, { package: package }))
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
        .pipe(header(config.banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.styles.output_path));
}

function build_scripts(done) {
    return gulp.src(config.scripts.src)
        .pipe(header(config.banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.scripts.output_path))
        .pipe(rename({ suffix: '.min' }))
        .pipe(uglify())
        .pipe(header(config.banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.scripts.output_path));
}

function build_iconfont() {
    const runTimestamp = Math.round(Date.now() / 1000);
    const fontName = 'chell-icons';

    return gulp.src(config.fonts.src)
        .pipe(iconfontCss({
            fontName: fontName,
            targetPath: '../../css/_icons.scss',
            fontPath: '../../fonts/',
            cssClass: 'fa'
        }))
        .pipe(iconfont({
            fontName: fontName,
            prependUnicode: false,
            normalize: true,
            formats: ['ttf', 'eot', 'woff', 'woff2', 'svg'],
            timestamp: runTimestamp,
        }))
        .pipe(gulp.dest('public/fonts/'));
}

function copy_robotofont() {
    return gulp.src(['node_modules/@fontsource/roboto/files/roboto-latin-300-normal.*'])
        .pipe(gulp.dest('public/fonts/'));
}

function watch() {
    gulp.watch('css/**/*.scss', gulp.series(['styles']));
    gulp.watch('js/**/*.js', gulp.series(['scripts']));
}

exports.scripts = gulp.series((done) => clean(done, 'js'), build_scripts);
exports.styles = gulp.series((done) => clean(done, 'css'), build_sass, build_styles);
exports.iconfont = gulp.series((done) => clean(done, 'iconfont'), build_iconfont);
exports.robotofont = gulp.series((done) => clean(done, 'robotofont'), copy_robotofont);
exports.default = gulp.parallel(exports.scripts, exports.robotofont, gulp.series(exports.iconfont, exports.styles));
exports.watch = watch;