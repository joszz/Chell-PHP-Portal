﻿module.exports = {
    banner: {
        main:
            '/*!' +
            ' <%= package.name %> v<%= package.version %>' +
            ' | (c) ' + new Date().getFullYear() + ' <%= package.author.name %>' +
            ' | <%= package.license %> License' +
            ' */\n'
    },
    output_path: 'public\\',
    styles: {
        output_path: 'css\\',
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
            'node_modules/chartjs-adapter-luxon/dist/chartjs-adapter-luxon.umd.js',
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
    },
    fonts: {
        output_path: 'fonts/',
        src: [
            'icons/vacuum-robot.svg',
            'icons/verisure-away.svg',
            'icons/verisure-disarmed.svg',
            'icons/verisure-stay.svg',

            'node_modules/@fortawesome/fontawesome-free/svgs/brands/chrome.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/brands/edge.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/brands/firefox.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/brands/safari.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/brands/internet-explorer.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/brands/opera.svg',

            'node_modules/@fortawesome/fontawesome-free/svgs/solid/arrows-up-down-left-right.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/backward.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/bolt.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/camera.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/chart-bar.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/check.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/chevron-down.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/chevron-left.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/chevron-right.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/chevron-up.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/circle-info.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/clapperboard.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/cloud-arrow-down.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/compress.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/expand.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/eye.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/fingerprint.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/floppy-disk.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/forward.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/gear.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/globe.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/image.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/key.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/list.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/location-dot.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/minus.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/pause.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/pen-to-square.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/play.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/plus.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/power-off.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/question.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/right-from-bracket.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/rotate.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/server.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/share.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/star.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/star-half.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/stop.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/temperature-half.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/trash-can.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/tv.svg',
            'node_modules/@fortawesome/fontawesome-free/svgs/solid/wrench.svg',
        ]
    }
};