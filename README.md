Chell PHP Portal
================
&copy; 2022, Jos Nienhuis

I created this project to have an easy to use portal for my homeserver. 
It aggregates different services and webapplications that I want easily accessible from within this portal.

# Video

[![Demo](https://img.youtube.com/vi/IzuMtewr6gc/0.jpg)](https://www.youtube.com/watch?v=IzuMtewr6gc)

# Screenshots

#### Home screenshot
![Home](https://raw.githubusercontent.com/joszz/Chell-PHP-Portal/master/img/screenshots/desktop_home.png "Home")
#### Menu screenshot
![Menu](https://raw.githubusercontent.com/joszz/Chell-PHP-Portal/master/img/screenshots/desktop_menu.png "Menu")
[More screenshots](https://github.com/joszz/Chell-PHP-Portal/tree/master/img/screenshots)

# Prerequisites
- [PHP 8.x](http://www.php.net/).
  - Required extensions 
    - [PSR](https://github.com/jbboehr/php-psr)
    - [Phalcon PHP framework 5.x](https://phalconphp.com/)
    - [Multibyte String](https://www.php.net/manual/en/book.mbstring.php)
    - [GD](https://www.php.net/manual/en/book.image.php)
    - [PDO](https://www.php.net/manual/en/book.pdo.php)
    - [PDO MySQL](https://www.php.net/manual/en/ref.pdo-mysql.php)
    - [Curl](https://www.php.net/manual/en/book.curl.php)
  - Optional
    - [SNMP](https://www.php.net/manual/en/book.snmp.php)
- [MySQL 5.x or higher](https://www.mysql.com/)
- [Apache 2.x](https://httpd.apache.org/)
  - Required modules
    - [mod_rewrite](https://httpd.apache.org/docs/2.4/mod/mod_rewrite.html)
    - [mod_headers](https://httpd.apache.org/docs/current/mod/mod_headers.html)

# Optional integrations
- [Couchpotato](https://couchpota.to//)
- [Duo](https://duo.com/)
- [HyperVAdmin](https://github.com/joszz/HyperVAdmin)
- [ImageProxy](https://github.com/willnorris/imageproxy)
- [Jellyfin](https://jellyfin.org/)
- [Kodi](https://kodi.tv/)
- [Motion](https://motion-project.github.io/)
- [PHPSysInfo](http://phpsysinfo.github.io/phpsysinfo/)
- [Pihole](https://pi-hole.net/)
- [Pulseway](https://www.pulseway.com/)
- [QBittorrent](https://www.qbittorrent.org/)
- [Redis](https://redis.io/)
- [Remote Control of PSA car](https://github.com/flobz/psa_car_controller)
- [Roborock](https://github.com/rytilahti/python-miio)
- [Sickrage](https://sickrage.github.io/)
- SNMP
- [Subsonic](http://www.subsonic.org/pages/index.jsp)
- [TMDB](https://www.themoviedb.org/)
- [Transmission](https://www.transmissionbt.com/)
- [Verisure](https://github.com/persandstrom/python-verisure)
- [WhatIsMyBrowser](https://www.whatismybrowser.com/)
- [Youless](https://www.youless.nl/)

# Installation and configuration

Before continuing, take a look at the prerequisites above. Those are required to run this project.

This project comes with an installer. Before you can use this installer, you will need to have some basic configuration done.

## Phalcon

The Phalcon PHP extension is required to run this project. [You can read about installing Phalcon here](https://docs.phalcon.io/4.0/en/installation).
To have Phalcon work, you will need to add the PSR extension to your php.ini, as described in the Phalcon installation URL. 

## Apache
Take a look at the required Apache modules and PHP extensions, to run this project, and enable those.

Since this project relies on .htaccess files to work correctly, you will also need to have this setup in your Apache configuration accordingly 
(either only for this project or server wide). 
[You can find how to do so by looking here for example](https://www.linode.com/docs/web-servers/apache/how-to-set-up-htaccess-on-apache/)

Make sure the user Apache runs under, has write access to the project's location. It will at least need access to the folder "app/logs" 
to write error logs to.

## Installer

When all the above steps are taken, you should be able to run the installer.
The installer will setup some basic stuff, such as the database and some default content.

You can run the installer by going to /install on the domain (and if applicable folder, defined by the baseUri).

When you fill all the details in correctly, you can press install and the site should take care of setting things up.

It will try to clean up after itself, but it won't be able to do so if the Apache user doesn't have write access in order to cleanup the following files;
- /app/controllers/InstallController.php
- /app/views/install/
- /sql/db-structure.sql

It's recommended to check if at least the InstallController is deleted after install (/app/controllers/InstallController.php), since it can be potentially harmfull.
The other files are cleaned up just for the sake of keeping things clean.