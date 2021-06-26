<?php
$validation = [
    'required'                      => 'Required',
    'not-a-number'                  => 'Not a number',
    'password-not-match'            => 'Password fields should match',
    'url'                           => 'Not a valid URL',
    'ip'                            => 'Not a valid IP',
    'email'                         => 'Not a valid e-mail',
    'mac'                           => 'Not a valid MAC address',
];

$help = [
    /* General settings */
    'application-title'                         => 'The title of this website, displayed in the header and the browsers tab.',
    'application-background'                    => 'The backgroundcolor for the website, either black or white.',
    'application-bgcolor-latitude'              => 'The latitude value to be used to determine the backgroundcolor based on the current time. After sunset and before sunrise the backgroundcolor will be set to light.',
    'application-bgcolor-longitude'             => 'The longitude value to be used to determine the backgroundcolor based on the current time. Between sunset and sunrise the backgroundcolor will be set to dark.',
    'application-alert_timeout'                 => 'The duration to display notifications after performing an action such as using WOL.',
    'application-items_per_page'                => 'The amount of items shown on paginated tables such as the logs tab in settings.',
    'application-phalcon_crypt_key'             => 'The key used for Phalcon\'s cryptographic functions.',
    'application-debug'                         => 'Whether debug mode is enabled; <ul><li>Sets PHP\'s display_error mode to on</li><li>Disables minification of JS and CSS</li><li>Outputs error information to the browser</li>',
    'application-demo_mode'                     => 'Whether the demo mode is enabled. Will blur all sensitive information when enabled.',
    'application-check_device_states_interval'  => 'The interval of refreshing the devices block on the dashboard.',

    'speedtest-what_is_my_browser_api_key'      => 'The API key used to access <a href="https://www.whatismybrowser.com/" target="_blank">WhatIsMyBrowser</a> information, used by the Speedtest plugin.',
    'speedtest-what_is_my_browser_api_url'      => 'The API URL used to access <a href="https://www.whatismybrowser.com/" target="_blank">WhatIsMyBrowser</a> information, used by the Speedtest plugin.',

    'duo-enabled'                               => 'Enables the <a href="https://duo.com/" target="_blank">DUO</a> 2 factor authentication plugin. Which will send a notification to your device when attempting to login.',
    'duo-apiHostname'                           => 'The DUO API url to authenticate against.',
    'duo-ikey'                                  => 'DUO integration key, <a href="https://duo.com/docs/duoweb" target="_blank">find more information at the DUO help pages.</a>',
    'duo-skey'                                  => 'DUO secret key, <a href="https://duo.com/docs/duoweb" target="_blank">find more information at the DUO help pages.</a>',
    'duo-akey'                                  => 'DUO akey, <a href="https://duo.com/docs/duoweb" target="_blank">find more information at the DUO help pages.</a>',

    'redis-enabled'                             => 'Enables the <a href="https://redis.io/" target="_blank">Redis server</a>.',
    'redis-host'                                => 'Specifies the host location of the Redis server.',
    'redis-port'                                => 'Specifies the port the Redis server is listening on. The default port is 6379.',
    'redis-auth'                                => 'Specifies the password to access the Redis server. Default installs leave this empty.',

    /* Dashboard related settings */
    'couchpotato-enabled'                       => 'Enables the <a href="https://couchpota.to/" target="_blank">Couchpotato</a> plugin.',
    'couchpotato-url'                           => 'The URL where Couchpotato can be found.',
    'couchpotato-api_key'                       => 'The API key used to access the Couchpotato data.',
    'couchpotato-rotate_interval'               => 'The interval used to cycle through the Couchpotato widget.',
    'couchpotato-tmdb_api_url'                  => 'The URL used to access TMDB API.',
    'couchpotato-tmdb_api_key'                  => 'The API key used to access TMDB information.',

    'hypervadmin-enabled'                       => 'Enables the <a href="https://github.com/joszz/HyperVAdmin" target="_blank">HyperVAdmin</a> plugin.',
    'hypervadmin-url'                           => 'The URL where HyperVAdmin can be found.',
    'hypervadmin-username'                      => 'The basicauth username to access HyperVAdmin.',
    'hypervadmin-password'                      => 'The basicauth password to access HyperVAdmin.',
    'hypervadmin-device'                        => 'Which device (configured in Chell) is the HyperVAdmin host. This will be used to determine where to place the button in the devices widget.',

    'imageproxy-enabled'                        => 'Enables the optional <a href="https://github.com/willnorris/imageproxy" target="_blank">imageproxy</a>.',
    'imageproxy-url'                            => 'The URL for the imageproxy.',

    'kodi-enabled'                              => 'Enables the Kodi plugins for latest movies, episodes and music albums.',
    'kodi-url'                                  => 'The URL where the Kodi webinterface can be found.',
    'kodi-username'                             => 'The basicauth username to access the Kodi webinterface.',
    'kodi-password'                             => 'The basicauth password to access the Kodi webinterface.',
    'kodi-rotate_movies_interval'               => 'The interval used to cycle through the Kodi latest movies widget.',
    'kodi-rotate_episodes_interval'             => 'The interval used to cycle through the Kodi latest episodes widget.',
    'kodi-rotate_albums_interval'               => 'The interval used to cycle through the Kodi latest albums widget.',
    'kodi-dbmusic'                              => 'The database used for Kodi\'s music storage',
    'kodi-dbvideo'                              => 'The database used for Kodi\'s video storage',
    'kodi-dbuser'                               => 'The database user for Kodi\'s storage',
    'kodi-dbpassword'                           => 'The database password for Kodi\'s storage',
    'kodi-dbhost'                               => 'The database host for Kodi\'s storage',

    'motion-enabled'                            => 'Enables <a href="https://motion-project.github.io/" target="_blank">Motion</a>.',
    'motion-url'                                => 'The URL for Motion.',
    'motion-picture_path'                       => 'The folder where the Motion pictures are stored in. Will retrieve the latest picture taken to show in the dashboard',
    'motion-update_interval'                    => 'The poll interval used to update the latest picture taken by Motion.',

    'opcache-enabled'                           => 'Enables the Opcache widget.',

    'phpsysinfo-enabled'                        => 'Enables <a href="https://github.com/phpsysinfo/phpsysinfo" target="_blank">PHPSysInfo</a>.',
    'phpsysinfo-url'                            => 'The base URL of your <a href="http://phpsysinfo.github.io/phpsysinfo/" target="_blank">PHPSysinfo installation</a>',
    'phpsysinfo-username'                       => 'Basicauth username needed when PHPSysInfo is protected by basicauth.',
    'phpsysinfo-password'                       => 'Basicauth password needed when PHPSysInfo is protected by basicauth.',

    'pihole-enabled'                            => 'Enables <a href="https://pi-hole.net/" target="_blank"PiHole</a> statistics.',
    'pihole-url'                                => 'The URL for PiHole admin interface.',

    'rcpu-enabled'                              => 'Enables the <a href="https://github.com/davidsblog/rCPU" target="_blank">rCPU</a> plugin.',
    'rcpu-url'                                  => 'The URL where rCPU is found.',

    'sickrage-enabled'                          => 'Enables the <a href="https://sickrage.github.io/" target="_blank">Sickrage</a> plugin.',
    'sickrage-url'                              => 'The URL where Sickrage can be found.',
    'sickrage-api_key'                          => 'The API key used to access the Sickrage data.',

    'speedtest-enabled'                         => 'Enables the <a href="" target="_blank">Speedtest</a> plugin.',
    'speedtest-test_order'                      => 'The order in which Speedtest performs it\'s tests.<br /><br /><ul><li>D=Download</li><li>U=Upload</li><li>P=Ping+Jitter</li><li>I=IP</li><li>_=1 second delay</li>',
    'speedtest-time_upload'                     => 'The time Speedtest will spent on testing the upload speed.',
    'speedtest-time_download'                   => 'The time Speedtest will spent on testing the download speed.',
    'speedtest-get_isp_info'                    => 'Whether the Speedtest plugin should show the IP of the client\'s ISP.',
    'speedtest-get_isp_distance'                => 'Which units Speedtest uses to show the distance to the ISP.',
    'speedtest-telemetry'                       => 'The telemetry to save.',
    'speedtest-ip_info_url'                     => 'The URL to access the IPInfo API.',
    'speedtest-ip_info_token'                   => 'The IPInfo API token used.',

    'subsonic-enabled'                          => 'Enables the <a href="http://www.subsonic.org/pages/index.jsp" target="_blank">Subsonic plugin.',
    'subsonic-url'                              => 'The URL where the Subsonic webclient is found.',
    'subsonic-username'                         => 'The username for Subsonic\'s login',
    'subsonic-password'                         => 'The password for Subsonic\'s login.',

    'transmission-enabled'                      => 'Enables the <a href="https://transmissionbt.com/" target="_blank">Transmission plugin.',
    'transmission-url'                          => 'The URL where the Transmission webclient is found.',
    'transmission-username'                     => 'Basicauth username needed when Transmission is protected by basicauth.',
    'transmission-password'                     => 'Basicauth password needed when Transmission is protected by basicauth.',
    'transmission-update_interval'              => 'The interval of refreshing the Transmission block on the dashboard',

    'youless-enabled'                           => 'Enables the Youless widget.',
    'youless-url'                               => 'The URL for Youless.',
    'youless-password'                          => 'Basicauth password needed when Youless is password protected.',
    'youless-update_interval'                   => 'The poll interval used to update the Youless current value.',
    'youless-threshold_primary'                 => 'The powerusage reported by Youless to consider as low.',
    'youless-threshold_warning'                 => 'The powerusage reported by Youless to consider as a warning.',
    'youless-threshold_danger'                  => 'The powerusage reported by Youless to consider as high.',

    'snmp-enabled'                              => 'Enables the SNMP widget.',
    'snmp-update_interval'                      => 'The poll interval used to update the SNMP values for the currently active host.',

    'verisure-enabled'                          => 'Enables the Verisure widget. Requires installation of <a href="https://github.com/persandstrom/python-verisure" target="_blank">python-verisure API</a>.',
    'verisure-update_interval'                  => 'The poll interval used to update the Verisure values. <br />Requests are being throttled when too many are made. Recommended to not go below 120 seconds.',
    'verisure-url'                              => 'The URL for the Verisure MyPages website (or any other URL you would like the button in the header to link to).',
    'verisure-username'                         => 'The Verisure MyPages username to authenticate to the API with.',
    'verisure-password'                         => 'The Verisure MyPages password to authenticate to the API with.',
    'verisure-securitycode'                     => 'The Verisure PIN to set the alarm with.',

    'roborock-enabled'                          => 'Enables the Roborock widget. Requires installation of <a href="https://github.com/rytilahti/python-miio" target="_blank">python-miio API</a>.',
    'roborock-update_interval'                  => 'The poll interval used to update the Roborock values.',
    'roborock-ip'                               => 'The IP address uses by python-miio to contact the Roborock API. Recommended to assign a static IP.',
    'roborock-token'                            => 'The Roborock API token. Find this token by following <a href="https://community.home-assistant.io/t/guide-to-retrieve-xiaomi-roborock-and-other-tokens/120174" target="_blank">this guide</a>, for example.',

    'jellyfin-enabled'                          => 'Enables the Jellyfin widget.',
    'jellyfin-url'                              => 'The Jellyfin URL',
    'jellyfin-userid'                           => 'The Jellyfin user id. This can be retrieved by looking at the XHR requests when opening Jellyfin\'s homepage.<br />Look in the network tab, filter by XHR type and search for "user".',
    'jellyfin-token'                            => 'The Jellyfin API token, specified in Jellyfin settings.',
    'jellyfin-views[]'                          => 'The Jellyfin libraries to show the latest items for.',
    'jellyfin-rotate_interval'                  => 'The interval used to cycle through the Jellyfin widget',

    'pulseway-enabled'                          => 'Enables the Pulseway widget.',
    'pulseway-url'                              => 'The Pulseway API base URL. See <a href="https://api.pulseway.com/" target="_blank">the documentation</a> for details',
    'pulseway-username'                         => 'The Pulseway API username.',
    'pulseway-password'                         => 'The Pulseway API password.',
    'pulseway-update_interval'                  => 'The poll interval used to update the Pulseway values.',
    'pulseway-systems[]'                        => 'The Pulseway systems to show in the widget. <br />Click the button with the PC screen icon to contact the API to retrieve the systems.<br />Make sure to save the URL, username and password first.',

    'databasestats-enabled'                     => 'Enables the database status widget.',
];

$helpTitles = [
    /* General settings */
    'application-title'                         => 'Title',
    'application-background'                    => 'Backgroundcolor',
    'application-background_latitude'           => 'Latitude',
    'application-background_longitude'          => 'Longitude',
    'application-alert_timeout'                 => 'Notification timeout',
    'application-items_per_page'                => 'Items per page',
    'application-phalcon_crypt_key'             => 'Phalcon cryptkey',
    'application-tmdb_api_key'                  => 'TMDB API key',
    'application-check_device_states_interval'  => 'Check device state interval',
    'application-demo_mode'                     => 'Demo mode',

    'speedtest-what_is_my_browser_api_key'      => 'WhatIsMyBrowser API key',
    'speedtest-what_is_my_browser_api_url'      => 'WhatIsMyBrowser API URL',

    'duo-enabled'                               => 'DUO 2 factor authentication',
    'duo-apiHostname'                           => 'DUO API hostname',
    'duo-ikey'                                  => 'DUO integration key',
    'duo-skey'                                  => 'DUO secret key',
    'duo-akey'                                  => 'DUO akey',

    'redis-enabled'                             => 'Redis',
    'redis-host'                                => 'Redis host',
    'redis-port'                                => 'Redis listening port',
    'redis-auth'                                => 'Redis password',

    /* Dashboard related settings */
    'couchpotato-enabled'                       => 'Couchpotato',
    'couchpotato-url'                           => 'Couchpotato URL',
    'couchpotato-api_key'                       => 'Couchpotato API key',
    'couchpotato-rotate_interval'               => 'Rotate Couchpotato widget interval',
    'couchpotato-tmdb_api_url'                  => 'TMDB API URL',
    'couchpotato-tmdb_api_key'                  => 'TMDB API key',

    'hypervadmin-enabled'                       => 'HyperVAdmin',
    'hypervadmin-url'                           => 'HyperVAdmin URL',
    'hypervadmin-username'                      => 'HyperVAdmin username',
    'hypervadmin-password'                      => 'HyperVAdmin password',
    'hypervadmin-device'                        => 'HyperVAdmin device',

    'imageproxy-enabled'                        => 'Imageproxy.',
    'imageproxy-url'                            => 'Imageproxy URL',

    'kodi-enabled'                              => 'Kodi',
    'kodi-url'                                  => 'Kodi URL',
    'kodi-username'                             => 'Kodi username',
    'kodi-password'                             => 'Kodi password',
    'kodi-rotate_movies_interval'               => 'Rotate Kodi movies widget interval',
    'kodi-rotate_episodes_interval'             => 'Rotate Kodi epsiodes widget interval',
    'kodi-rotate_albums_interval'               => 'Rotate Kodi albums widget interval',
    'kodi-dbmusic'                              => 'Kodi music database',
    'kodi-dbvideo'                              => 'Kodi video database',
    'kodi-dbuser'                               => 'Kodi database user',
    'kodi-dbpassword'                           => 'Kodi database password',
    'kodi-dbhost'                               => 'Kodi database host',

    'motion-enabled'                            => 'Motion',
    'motion-url'                                => 'Motion URL',
    'motion-picture_path'                       => 'Motion picture path',
    'motion-update_interval'                    => 'Motion update interval',

    'opcache-enabled'                           => 'Opcache',

    'phpsysinfo-enabled'                        => 'PHPSysInfo',
    'phpsysinfo-url'                            => 'PHPSysInfo URL',
    'phpsysinfo-username'                       => 'PHPSysInfo username',
    'phpsysinfo-password'                       => 'PHPSysInfo password',

    'pihole-enabled'                            => 'PiHole',
    'pihole-url'                                => 'PiHole URL',

    'rcpu-enabled'                              => 'rCPU',
    'rcpu-url'                                  => 'rCPU URL',

    'sickrage-enabled'                          => 'Sickrage',
    'sickrage-url'                              => 'Sickrage URL',
    'sickrage-api_key'                          => 'Sickrage API key',

    'speedtest-enabled'                         => 'Speedtest',
    'speedtest-test_order'                      => 'Speedtest test order',
    'speedtest-time_upload'                     => 'Speedtest upload time',
    'speedtest-time_download'                   => 'Speedtest download time',
    'speedtest-get_isp_info'                    => 'Speedtest show ISP IP',
    'speedtest-get_isp_distance'                => 'Speedtest distance units',
    'speedtest-telemetry'                       => 'Speedtest telemetry',
    'speedtest-ip_info_url'                     => 'IPInfo API URL',
    'speedtest-ip_info_token'                   => 'IPInfo API token',

    'subsonic-enabled'                          => 'Subsonic',
    'subsonic-url'                              => 'Subsonic URL',
    'subsonic-username'                         => 'Subsonic username',
    'subsonic-password'                         => 'Subsonic password',

    'transmission-enabled'                      => 'Transmission',
    'transmission-url'                          => 'Transmission URL',
    'transmission-username'                     => 'Transmission username',
    'transmission-password'                     => 'Transmission password',
    'transmission-update_interval'              => 'Transmission update interval',

    'youless-enabled'                           => 'Youless',
    'youless-url'                               => 'Youless URL',
    'youless-password'                          => 'Youless password.',
    'youless-update_interval'                   => 'Youless update interval',
    'youless-threshold_primary'                 => 'Youless primary threshold',
    'youless-threshold_warning'                 => 'Youless warn threshold',
    'youless-threshold_danger'                  => 'Youless danger threshold',

    'snmp-enabled'                              => 'SNMP',
    'snmp-update_interval'                      => 'SNMP update interval',

    'verisure-enabled'                          => 'Verisure',
    'verisure-update_interval'                  => 'Verisure update interval',
    'verisure-url'                              => 'Verisure URL',
    'verisure-username'                         => 'Verisure username.',
    'verisure-password'                         => 'Verisure password',
    'verisure-securitycode'                     => 'Verisure PIN',

    'roborock-enabled'                          => 'Roborock',
    'roborock-update_interval'                  => 'Roborock update interval',
    'roborock-ip'                               => 'Roborock IP',
    'roborock-token'                            => 'Roborock API token',

    'jellyfin-enabled'                          => 'Jellyfin',
    'jellyfin-url'                              => 'Jellyfin URL',
    'jellyfin-userid'                           => 'Jellyfin user id',
    'jellyfin-token'                            => 'Jellyfin API token',
    'jellyfin-views[]'                          => 'Jellyfin libraries',
    'jellyfin-rotate_interval'                  => 'Rotate Jellyfin widget interval',

    'pulseway-enabled'                          => 'Pulseway',
    'pulseway-url'                              => 'Pulseway URL',
    'pulseway-username'                         => 'Pulseway username',
    'pulseway-password'                         => 'Pulseway password',
    'pulseway-update_interval'                  => 'Pulseway update interval',
    'pulseway-systems[]'                        => 'Pulseway systems',

    'databasestats-enabled'                     => 'Database status',
];