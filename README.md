Chell PHP Portal
================
&copy; 2021, Jos Nienhuis

I created this project to have an easy to use portal for my homeserver. 
It aggregates different services and webapplications that I want easily accessible from within this portal.

# Video

[![Demo](https://raw.githubusercontent.com/joszz/Chell-PHP-Portal/master/screenshots/video.jpg)](https://www.youtube.com/watch?v=IzuMtewr6gc)

# Screenshots

#### Home screenshot
![Home](https://raw.githubusercontent.com/joszz/Chell-PHP-Portal/master/screenshots/desktop_home.jpg "Home")
#### Menu screenshot
![Menu](https://raw.githubusercontent.com/joszz/Chell-PHP-Portal/master/screenshots/desktop_menu.jpg "Menu")
[More screenshots](https://github.com/joszz/Chell-PHP-Portal/tree/master/screenshots)

# Prerequisites
- [PHP 7.2 or higher](http://www.php.net/)
- [MySQL 5.x](https://www.mysql.com/)
- [Phalcon PHP framework 4.x](https://phalconphp.com/)
- [PSR PHP extension](https://github.com/jbboehr/php-psr)
- [Apache 2.x](https://httpd.apache.org/)
  - The Apache rewrite module needs to be enabled
  - The Apache headers module needs to be enabled

# Optional integrations
- [PHPSysInfo](http://phpsysinfo.github.io/phpsysinfo/)
- [rCPU](https://github.com/davidsblog/rCPU)
- [Transmission](https://www.transmissionbt.com/)
- [Sickrage](https://sickrage.github.io/)
- [Couchpotato](https://couchpota.to//)
- [Subsonic](http://www.subsonic.org/pages/index.jsp)
- [Kodi](https://kodi.tv/)
- [HyperVAdmin](https://github.com/joszz/HyperVAdmin)
- [Motion](https://motion-project.github.io/)
- [Pihole](https://pi-hole.net/)
- [ImageProxy](https://github.com/willnorris/imageproxy)
- [Duo](https://duo.com/)
- [Redis](https://redis.io/)
- [WhatIsMyBrowser](https://www.whatismybrowser.com/)
- [TMDB](https://www.themoviedb.org/)

# Installation and configuration

Before continuing, take a look at the prerequisites above. These are required to run this project.

This project comes with an installer. Before you can use this installer, you will need to have basic configuration done.

## Phalcon

The Phalcon PHP extension is required to run this project. [You can read about installing Phalcon here](https://docs.phalcon.io/4.0/en/installation).
To have Phalcon work, you will need to add the PSR extension to your php.ini, as described in the Phalcon installation URL. 

## Apache
Take a look at the required Apache modules to run this project, and enable these.

Since this project relies on .htaccess files to work correctly, you will also need to have this setup in your Apache configuration accordingly 
(either only for this project or server wide). 
[You can find how to do so by looking here for example](https://www.linode.com/docs/web-servers/apache/how-to-set-up-htaccess-on-apache/)

Make sure the user Apache runs under, has write access to the project's location. It will at least need access to the folder "app/logs" (which doesn't exist on install) 
to write error logs to. And will also need write permissions to the file "app/config/config.ini".

## Config.ini
If you decide to run this project outside of the root of the domain, you will need to edit "app/config/config.ini". Adjust the baseUri (in section Application) to match
the directory you run this project from.

## Installer

When all the above steps are taken, you should be able to run the installer.
The installer will setup some basic stuff, such as the database and some default content.

You can run the installer by going to /install on the domain (and if applicable folder, defined by the baseUri).

When you fill all the details in correctly, you can press install and the site should take care of setting things up.

It will try to clean up after itself, but it won't be able to do so if the Apache user doesn't have write access in order to cleanup the following files;
- /app/controllers/IndexController.php
- /app/views/install/
- /db-structure.sql

It's recommended to check if at least the IndexController is deleted after install (/app/controllers/IndexController.php), since it can be potentially harmfull.
The other files are cleaned up just for the sake of keeping things clean.