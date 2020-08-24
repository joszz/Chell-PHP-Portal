<?php

$help = [
    /* General settings */
    'title'                         => 'The title of this website, displayed in the header and the browsers tab.',
    'bgcolor'                       => 'The backgroundcolor for the website, either black or white.',
    'alert-timeout'                 => 'The duration to display notifications after performing an action such as using WOL.',
    'items-per-page'                => 'The amount of items shown on paginated tables such as the logs tab in settings.',
    'cryptkey'                      => 'The key used for Phalcon\'s cryptographic functions.',
    'tmdb-apikey'                   => 'The API key used to access TMDB information, used by the Couchpotato plugin.',
    'debug'                         => 'Whether debug mode is enabled; <ul><li>Sets PHP\'s display_error mode to on</li><li>Disables minification of JS and CSS</li><li>Outputs error information to the browser</li>',
    'demo'                          => 'Whether the demo mode is enabled. Will blur all sensitive information when enabled.',
    'check-devicestate-interval'    => 'The interval of refreshing the devices block on the dashboard.',

    'whatismybrowser-apikey'        => 'The API key used to access <a href="https://www.whatismybrowser.com/" target="_blank">WhatIsMyBrowser</a> information, used by the Speedtest plugin.',
    'whatismybrowser-apiurl'        => 'The API URL used to access <a href="https://www.whatismybrowser.com/" target="_blank">WhatIsMyBrowser</a> information, used by the Speedtest plugin.',

    'duo-enabled'                   => 'Enables the <a href="https://duo.com/" target="_blank">DUO</a> 2 factor authentication plugin. Which will send a notification to your device when attempting to login.',
    'duo-apiHostname'               => 'The DUO API url to authenticate against.',
    'duo-ikey'                      => 'DUO integration key, <a href="https://duo.com/docs/duoweb" target="_blank">find more information at the DUO help pages.</a>',
    'duo-skey'                      => 'DUO secret key, <a href="https://duo.com/docs/duoweb" target="_blank">find more information at the DUO help pages.</a>',
    'duo-akey'                      => 'DUO akey, <a href="https://duo.com/docs/duoweb" target="_blank">find more information at the DUO help pages.</a>',

    'redis-enabled'                 => 'Enables the <a href="https://redis.io/" target="_blank">Redis server</a>.',
    'redis-host'                    => 'Specifies the host location of the Redis server.',
    'redis-port'                    => 'Specifies the port the Redis server is listening on. The default port is 6379.',
    'redis-auth'                    => 'Specifies the password to access the Redis server. Default installs leave this empty.',

    /* Dashboard related settings */
    'couchpotato-enabled'           => 'Enables the <a href="https://couchpota.to/" target="_blank">Couchpotato</a> plugin.',
    'couchpotato-url'               => 'The URL where Couchpotato can be found.',
    'couchpotato-apikey'            => 'The API key used to access the Couchpotato data.',
    'couchpotato-rotate-interval'   => 'The interval used to cycle through the Couchpotato widget.',

    'hypervadmin-enabled'           => 'Enables the <a href="https://github.com/joszz/HyperVAdmin" target="_blank">HyperVAdmin</a> plugin.',
    'hypervadmin-url'               => 'The URL where HyperVAdmin can be found.',
    'hypervadmin-username'          => 'The basicauth username to access HyperVAdmin.',
    'hypervadmin-password'          => 'The basicauth password to access HyperVAdmin.',
    'hypervadmin-device'            => 'Which device (configured in Chell) is the HyperVAdmin host. This will be used to determine where to place the button in the devices widget.',

    'imageproxy-enabled'            => 'Enables the optional <a href="https://github.com/willnorris/imageproxy" target="_blank">imageproxy</a>.',
    'imageproxy-url'                => 'The URL for the imageproxy.',

    'kodi-enabled'                  => 'Enables the Kodi plugins for latest movies, episodes and music albums.',
    'kodi-url'                      => 'The URL where the Kodi webinterface can be found.',
    'kodi-username'                 => 'The basicauth username to access the Kodi webinterface.',
    'kodi-password'                 => 'The basicauth password to access the Kodi webinterface.',
    'kodi-rotate-movies-interval'   => 'The interval used to cycle through the Kodi latest movies widget.',
    'kodi-rotate-episodes-interval' => 'The interval used to cycle through the Kodi latest episodes widget.',
    'kodi-rotate-albums-interval'   => 'The interval used to cycle through the Kodi latest albums widget.',

    'motion-enabled'                => 'Enables <a href="https://motion-project.github.io/" target="_blank">Motion</a>.',
    'motion-url'                    => 'The URL for Motion.',
    'motion-picturepath'            => 'The folder where the Motion pictures are stored in. Will retrieve the latest picture taken to show in the dashboard',
    'motion-update-interval'        => 'The poll interval used to update the latest picture taken by Motion.',

    'opcache-enabled'               => 'Enables Opcache widget.',

    'phpsysinfo-enabled'            => 'Enables <a href="https://github.com/phpsysinfo/phpsysinfo" target="_blank">PHPSysInfo</a>.',
    'phpsysinfo-url'                => 'The base URL of your <a href="http://phpsysinfo.github.io/phpsysinfo/" target="_blank">PHPSysinfo installation</a>',
    'phpsysinfo-username'           => 'Basicauth username needed when PHPSysInfo is protected by basicauth.',
    'phpsysinfo-password'           => 'Basicauth password needed when PHPSysInfo is protected by basicauth.',

    'pihole-enabled'                => 'Enables <a href="https://pi-hole.net/" target="_blank"PiHole</a> statistics.',
    'pihole-url'                    => 'The URL for PiHole admin interface.',

    'rcpu-enabled'                  => 'Enables the <a href="https://github.com/davidsblog/rCPU" target="_blank">rCPU</a> plugin.',
    'rcpu-url'                      => 'The URL where rCPU is found.',

    'sickrage-enabled'              => 'Enables the <a href="https://sickrage.github.io/" target="_blank">Sickrage</a> plugin.',
    'sickrage-url'                  => 'The URL where Sickrage can be found.',
    'sickrage-apikey'               => 'The API key used to access the Sickrage data.',

    'speedtest-enabled'             => 'Enables the <a href="" target="_blank">Speedtest</a> plugin.',
    'speedtest-test-order'          => 'The order in which Speedtest performs it\'s tests.<br /><br /><ul><li>D=Download</li><li>U=Upload</li><li>P=Ping+Jitter</li><li>I=IP</li><li>_=1 second delay</li>',
    'speedtest-time-ul'             => 'The time Speedtest will spent on testing the upload speed.',
    'speedtest-time-dl'             => 'The time Speedtest will spent on testing the download speed.',
    'speedtest-get-ispip'           => 'Whether the Speedtest plugin should show the IP of the client\'s ISP.',
    'speedtest-isp-info-distance'   => 'Which units Speedtest uses to show the distance to the ISP.',
    'speedtest-telemetry'           => 'The telemetry to save.',
    'speedtest-ipinfo-url'          => 'The URL to access the IPInfo API.',
    'speedtest-ipinfo-token'        => 'The IPInfo API token used.',

    'subsonic-enabled'              => 'Enables the <a href="http://www.subsonic.org/pages/index.jsp" target="_blank">Subsonic plugin.',
    'subsonic-url'                  => 'The URL where the Subsonic webclient is found.',
    'subsonic-username'             => 'The username for Subsonic\'s login',
    'subsonic-password'             => 'The password for Subsonic\'s login.',

    'transmission-enabled'          => 'Enables the <a href="https://transmissionbt.com/" target="_blank">Transmission plugin.',
    'transmission-url'              => 'The URL where the Transmission webclient is found.',
    'transmission-username'         => 'Basicauth username needed when Transmission is protected by basicauth.',
    'transmission-password'         => 'Basicauth password needed when Transmission is protected by basicauth.',
    'transmission-update-interval'  => 'The interval of refreshing the Transmission block on the dashboard',

    'youless-enabled'               => 'Enables the Youless widget.',
    'youless-url'                   => 'The URL for Youless.',
    'youless-password'              => 'Basicauth password needed when Youless is password protected.',
    'youless-update-interval'       => 'The poll interval used to update the Youless current value.',
    'youless-primary-threshold'     => 'The powerusage reported by Youless to consider as low.',
    'youless-warn-threshold'        => 'The powerusage reported by Youless to consider as a warning.',
    'youless-danger-threshold'      => 'The powerusage reported by Youless to consider as high.',

    'snmp-enabled'                  => 'Enables the SNMP widget.',
    'snmp-update-interval'          => 'The poll interval used to update the SNMP values for the currently active host.',

    'verisure-enabled'              => 'Enables the Verisure widget.',
    'verisure-update-interval'      => 'The poll interval used to update the Verisure values. <br />Requests are being throttled when too many are made. Recommended to not go below 120 seconds.',
    'verisure-url'                  => 'The URL for the Verisure MyPages website (or any other URL you would like the button in the header to link to).',
    'verisure-username'             => 'The Verisure MyPages username to authenticate to the API with.',
    'verisure-password'             => 'The Verisure MyPages password to authenticate to the API with.',
];

$helpTitles = [
    /* General settings */
    'title'                         => 'Title',
    'bgcolor'                       => 'Backgroundcolor',
    'alert-timeout'                 => 'Notification timeout',
    'items-per-page'                => 'Items per page',
    'cryptkey'                      => 'Phalcon cryptkey',
    'tmdb-apikey'                   => 'TMDB API key',
    'check-devicestate-interval'    => 'Check device state interval',
    'debug'                         => 'Debug mode',
    'demo'                          => 'Demo mode',

    'whatismybrowser-apikey'        => 'WhatIsMyBrowser API key',
    'whatismybrowser-apiurl'        => 'WhatIsMyBrowser API URL',

    'duo-enabled'                   => 'DUO 2 factor authentication',
    'duo-apiHostname'               => 'DUO API hostname',
    'duo-ikey'                      => 'DUO integration key',
    'duo-skey'                      => 'DUO secret key',
    'duo-akey'                      => 'DUO akey',

    'redis-enabled'                 => 'Redis',
    'redis-host'                    => 'Redis host',
    'redis-port'                    => 'Redis listening port',
    'redis-auth'                    => 'Redis password',

    /* Dashboard related settings */
    'couchpotato-enabled'           => 'Couchpotato',
    'couchpotato-url'               => 'Couchpotato URL',
    'couchpotato-apikey'            => 'Couchpotato API key',
    'couchpotato-rotate-interval'   => 'Rotate Couchpotato widget interval',

    'hypervadmin-enabled'           => 'HyperVAdmin',
    'hypervadmin-url'               => 'HyperVAdmin URL',
    'hypervadmin-username'          => 'HyperVAdmin username',
    'hypervadmin-password'          => 'HyperVAdmin password',
    'hypervadmin-device'            => 'HyperVAdmin device',

    'imageproxy-enabled'            => 'Imageproxy.',
    'imageproxy-url'                => 'Imageproxy URL',

    'kodi-enabled'                  => 'Kodi',
    'kodi-url'                      => 'Kodi URL',
    'kodi-username'                 => 'Kodi username',
    'kodi-password'                 => 'Kodi password',
    'kodi-rotate-movies-interval'   => 'Rotate Kodi movies widget interval',
    'kodi-rotate-episodes-interval' => 'Rotate Kodi epsiodes widget interval',
    'kodi-rotate-albums-interval'   => 'Rotate Kodi albums widget interval',

    'motion-enabled'                => 'Motion',
    'motion-url'                    => 'Motion URL',
    'motion-picturepath'            => 'Motion picture path',
    'motion-update-interval'        => 'Motion update interval',

    'opcache-enabled'               => 'Opcache',

    'phpsysinfo-enabled'            => 'PHPSysInfo',
    'phpsysinfo-url'                => 'PHPSysInfo URL',
    'phpsysinfo-username'           => 'PHPSysInfo username',
    'phpsysinfo-password'           => 'PHPSysInfo password',

    'pihole-enabled'                => 'PiHole',
    'pihole-url'                    => 'PiHole URL',

    'rcpu-enabled'                  => 'rCPU',
    'rcpu-url'                      => 'rCPU URL',

    'sickrage-enabled'              => 'Sickrage',
    'sickrage-url'                  => 'Sickrage URL',
    'sickrage-apikey'               => 'Sickrage API key',

    'speedtest-enabled'             => 'Speedtest',
    'speedtest-test-order'          => 'Speedtest test order',
    'speedtest-time-ul'             => 'Speedtest upload time',
    'speedtest-time-dl'             => 'Speedtest download time',
    'speedtest-get-ispip'           => 'Speedtest show ISP IP',
    'speedtest-isp-info-distance'   => 'Speedtest distance units',
    'speedtest-telemetry'           => 'Speedtest telemetry',
    'speedtest-ipinfo-url'          => 'IPInfo API URL',
    'speedtest-ipinfo-token'        => 'IPInfo API token',

    'subsonic-enabled'              => 'Subsonic',
    'subsonic-url'                  => 'Subsonic URL',
    'subsonic-username'             => 'Subsonic username',
    'subsonic-password'             => 'Subsonic password',

    'transmission-enabled'          => 'Transmission',
    'transmission-url'              => 'Transmission URL',
    'transmission-username'         => 'Transmission username',
    'transmission-password'         => 'Transmission password',
    'transmission-update-interval'  => 'Transmission update interval',

    'youless-enabled'               => 'Youless',
    'youless-url'                   => 'Youless URL',
    'youless-password'              => 'Youless password.',
    'youless-update-interval'       => 'Youless update interval',
    'youless-primary-threshold'     => 'Youless primary threshold',
    'youless-warn-threshold'        => 'Youless warn threshold',
    'youless-danger-threshold'      => 'Youless danger threshold',

    'snmp-enabled'                  => 'SNMP',
    'snmp-update-interval'          => 'SNMP update interval',

    'verisure-enabled'              => 'Verisure',
    'verisure-update-interval'      => 'Verisure update interval',
    'verisure-url'                  => 'Verisure URL',
    'verisure-username'             => 'Verisure username.',
    'verisure-password'             => 'Verisure password',
];