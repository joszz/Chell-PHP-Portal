<?php

$help = [
    'title'                         => 'The title of this website, displayed in the header and the browsers tab.',
    'bgcolor'                       => 'The backgroundcolor for the website, either black or white.',
    'alert-timeout'                 => 'The duration to display notifications after performing an action such as using WOL.',
    'cryptkey'                      => 'The key used for Phalcon\'s cryptographic functions.',
    'tmdb-apikey'                   => 'The API key used to access TMDB information, used by the Couchpotato plugin.',
    'debug'                         => 'Whether debug mode is enabled; <ul><li>Sets PHP\'s display_error mode to on</li><li>Disables minification of JS and CSS</li><li>Outputs error information to the browser</li>',
    'duo-enabled'                   => 'Enables the <a href="https://duo.com/" target="_blank">DUO</a> 2 factor authentication plugin. Which will send a notification to your device when attempting to login.',
    'duo-apiHostname'               => 'The DUO API url to authenticate against.',
    'duo-ikey'                      => 'DUO integration key, <a href="https://duo.com/docs/duoweb" target="_blank">find more information at the DUO help pages.</a>',
    'duo-skey'                      => 'DUO secret key, <a href="https://duo.com/docs/duoweb" target="_blank">find more information at the DUO help pages.</a>',
    'duo-akey'                      => 'DUO akey, <a href="https://duo.com/docs/duoweb" target="_blank">find more information at the DUO help pages.</a>',
    'check-devicestate-interval'    => 'The interval of refreshing the devices block on the dashboard.',
    'phpsysinfo-url'                => 'The base URL of your <a href="http://phpsysinfo.github.io/phpsysinfo/" target="_blank">PHPSysinfo installation</a>',
    'phpsysinfo-username'           => 'Basicauth username needed when PHPSysInfo is protected by basicauth.',
    'phpsysinfo-password'           => 'Basicauth password needed when PHPSysInfo is protected by basicauth.',
    'rcpu-enabled'                  => 'Enables the <a href="https://github.com/davidsblog/rCPU" target="_blank">rCPU</a> plugin.',
    'rcpu-url'                      => 'The URL where rCPU is found.',
    'transmission-enabled'          => 'Enables the <a href="https://transmissionbt.com/" target="_blank">Transmission plugin.',
    'transmission-url'              => 'The URL where the Transmission webclient is found.',
    'transmission-username'         => 'Basicauth username needed when Transmission is protected by basicauth.',
    'transmission-password'         => 'Basicauth password needed when Transmission is protected by basicauth.',
    'transmission-update-interval'  => 'The interval of refreshing the Transmission block on the dashboard',
    'subsonic-enabled'              => 'Enables the <a href="http://www.subsonic.org/pages/index.jsp" target="_blank">Subsonic plugin.',
    'subsonic-url'                  => 'The URL where the Subsonic webclient is found.',
    'subsonic-username'             => 'The username for Subsonic\'s login',
    'subsonic-password'             => 'The password for Subsonic\'s login.',
    'kodi-enabled'                  => 'Enables the Kodi plugins for latest movies, episodes and music albums.',
    'kodi-url'                      => 'The URL where the Kodi webinterface can be found.',
    'kodi-username'                 => 'The basicauth username to access the Kodi webinterface.',
    'kodi-password'                 => 'The basicauth password to access the Kodi webinterface.',
    'rotate-movies-interval'        => 'The interval used to cycle through the Kodi latest movies widget.',
    'rotate-episodes-interval'      => 'The interval used to cycle through the Kodi latest episodes widget.',
    'rotate-albums-interval'        => 'The interval used to cycle through the Kodi latest albums widget.',
    'sickrage-enabled'              => 'Enables the <a href="https://sickrage.github.io/" target="_blank">Sickrage</a> plugin.',
    'sickrage-url'                  => 'The URL where Sickrage can be found.',
    'sickrage-apikey'               => 'The API key used to access the Sickrage data.',
    'couchpotato-enabled'           => 'Enables the <a href="https://couchpota.to/" target="_blank">Couchpotato</a> plugin.',
    'couchpotato-url'               => 'The URL where Couchpotato can be found.',
    'couchpotato-apikey'            => 'The API key used to access the Couchpotato data.',
    'couchpotato-rotate-interval'   => 'The interval used to cycle through the Couchpotato widget.',
    'hypervadmin-enabled'           => 'Enables the <a href="https://github.com/joszz/HyperVAdmin" target="_blank">HyperVAdmin</a> plugin.',
    'hypervadmin-url'               => 'The URL where HyperVAdmin can be found.',
    'hypervadmin-username'          => 'The basicauth username to access HyperVAdmin.',
    'hypervadmin-password'          => 'The basicauth password to access HyperVAdmin.',
    'hypervadmin-host'              => 'Which device (configured in Chell) is the HyperVAdmin host. This will be used to determine where to place the button in the devices widget.'
];

$helpTitles = [
    'title'                         => 'Title',
    'bgcolor'                       => 'Backgroundcolor',
    'alert-timeout'                 => 'Notification timeout',
    'cryptkey'                      => 'Phalcon cryptkey',
    'tmdb-apikey'                   => 'TMDB API key',
    'debug'                         => 'Debug mode',
    'duo-enabled'                   => 'DUO 2 factor authentication',
    'duo-apiHostname'               => 'DUO API hostname',
    'duo-ikey'                      => 'DUO integration key',
    'duo-skey'                      => 'DUO secret key',
    'duo-akey'                      => 'DUO akey',
    'check-devicestate-interval'    => 'Check device state interval',
    'phpsysinfo-url'                => 'PHPSysInfo URL',
    'phpsysinfo-username'           => 'PHPSysInfo username',
    'phpsysinfo-password'           => 'PHPSysInfo password',
    'rcpu-enabled'                  => 'rCPU',
    'rcpu-url'                      => 'rCPU URL',
    'transmission-enabled'          => 'Transmission',
    'transmission-url'              => 'Transmission URL',
    'transmission-username'         => 'Transmission username',
    'transmission-password'         => 'Transmission password',
    'transmission-update-interval'  => 'Transmission update interval',
    'subsonic-enabled'              => 'Subsonic',
    'subsonic-url'                  => 'Subsonic URL',
    'subsonic-username'             => 'Subsonic username',
    'subsonic-password'             => 'Subsonic password',
    'kodi-enabled'                  => 'Kodi',
    'kodi-username'                 => 'Kodi username',
    'kodi-password'                 => 'Kodi password',
    'rotate-movies-interval'        => 'Rotate Kodi movies widget interval',
    'rotate-episodes-interval'      => 'Rotate Kodi epsiodes widget interval',
    'rotate-albums-interval'        => 'Rotate Kodi albums widget interval',
    'sickrage-enabled'              => 'Sickrage',
    'sickrage-url'                  => 'Sickrage URL',
    'sickrage-apikey'               => 'Sickrage API key',
    'couchpotato-enabled'           => 'Couchpotato',
    'couchpotato-url'               => 'Couchpotato URL',
    'couchpotato-apikey'            => 'Couchpotato API key',
    'couchpotato-rotate-interval'   => 'Rotate Couchpotato widget interval',
    'hypervadmin-enabled'           => 'HyperVAdmin',
    'hypervadmin-url'               => 'HyperVAdmin URL',
    'hypervadmin-username'          => 'HyperVAdmin username',
    'hypervadmin-password'          => 'HyperVAdmin password',
    'hypervadmin-host'              => 'HyperVAdmin host'
];