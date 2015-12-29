Chell PHP Portal
================
Copyright (c), 2015-2016, Jos Nienhuis (jos_nienhuis@hotmail.com)

This project I created to have a easy to use portal for my homeserver. 
It aggregates different services and webapplications that I want easily accessible from within this portal.
The current state of the project is still very much a work in progress. 
Some functionality might not yet be fully decoupled from my own setup but the goal is to work toward this and have a generic solution for anyone.

Screenshots
-----------
![Home](master/screenshots/home.png?raw=true "Home")

Prerequisites
-------------
- PHP 5.x
- MySQL 5.x
- [Phalcon PHP framework 2.x](https://phalconphp.com/)
- Apache 2.x
- [rCPU](https://github.com/davidsblog/rCPU)

Installation and configuration
------------------------------
First you will need to import the DB structure in MySQL, provided in the root of this project.
Later in this projects development there will be a GUI provided for installation and also configuration of the portal items. 
For now this would have to be manually edited through the database. You can find information about the tables and associated columns below.

Next copy over source files to a directory on your server, preferrably (for security considerations) outside of the DocumentRoot directory of your webserver. The "app" directory of this project should NOT be accessible from the web. The "public" directory however, should be. This can be achieved with some Apache configuration, setting up an alias etc. I will not go into detail (for now) on how to achieve this.

Finally you can edit the config.ini file. You can find this file in "app/config/config.ini", the values to be specified speak for themselves.
