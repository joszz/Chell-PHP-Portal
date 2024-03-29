/// <binding ProjectOpened='watch' />
const gulp = require('gulp');
const sass = require('gulp-dart-sass');
const uglify = require('gulp-uglify');
const sourcemaps = require('gulp-sourcemaps');
const del = require('del');
const cssnano = require('gulp-cssnano');
const header = require('gulp-header');
const rename = require('gulp-rename');
const iconfont = require('gulp-iconfont');
const iconfontCss = require('gulp-iconfont-css');
const clc = require('cli-color');
const run = require('gulp-run-command').default;
const package = require('./package.json');
const config = require("./gulpfile_config.js");
const configdev = require("./gulpfile_configdev.js");

function clean(which) {
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
}

function copyProduction(path) {
    console.log(clc.green(`Copying to production: ${path}`));
    gulp.src(path).pipe(gulp.dest(configdev.production_path));
}

function deleteProduction(path) {
    console.log(clc.red(`Deleting from production: ${path}`));
    del(configdev.production_path + path, { force: true });
}

function buildScript(path) {
    console.log(clc.green(`Building script: ${path}`));
    gulp.src(path)
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(header(config.banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path))
        .pipe(rename({ suffix: '.min' }))
        .pipe(uglify())
        .pipe(header(config.banner.main, { package: package }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(config.output_path));
}

gulp.task('sass', () => {
    clean('css');
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
});


gulp.task('styles', () => {
    return gulp.src(config.styles.css)
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(header(config.banner.main, { package: package }))
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
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(config.output_path + config.styles.output_path));
});

gulp.task('scripts', () => {
    clean('js');
    return gulp.src(config.scripts.src)
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(header(config.banner.main, { package: package }))
        .pipe(gulp.dest(config.output_path + config.scripts.output_path))
        .pipe(rename({ suffix: '.min' }))
        .pipe(uglify())
        .pipe(header(config.banner.main, { package: package }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(config.output_path + config.scripts.output_path));
});

gulp.task('iconfont', () => {
    clean('iconfont');
    const runTimestamp = Math.round(Date.now() / 1000);
    const fontName = 'chell-icons';

    return gulp.src(config.fonts.src)
        .pipe(iconfontCss({
            fontName: fontName,
            targetPath: '../../css/_icons.scss',
            fontPath: '../fonts/',
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
});

gulp.task('robotofont', () => {
    clean('robotofont')
    return gulp.src(['node_modules/@fontsource/roboto/files/roboto-latin-300-normal.*'])
        .pipe(gulp.dest('public/fonts/'));
});

gulp.task('genrate_migration', run(`vendor/bin/phalcon-migrations generate --config=app/migrations/Config.php --version=${package.version} --skip-ref-schema --no-auto-increment`));

gulp.task('watch', () => {
    gulp.watch('css/**/*.scss', gulp.series(['sass', 'styles']));
    gulp.watch('js/**/*.js')
        .on("change", buildScript)
        .on("add", buildScript);
    gulp.watch('**')
        .on("change", copyProduction)
        .on("add", copyProduction)
        .on("addDir", copyProduction)
        .on("unlink", deleteProduction)
        .on("unlinkDir", deleteProduction);
});