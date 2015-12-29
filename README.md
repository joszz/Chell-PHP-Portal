Chell PHP Portal
================
Copyright (c), 2015-2016, Jos Nienhuis (jos_nienhuis@hotmail.com)

I created this project to have an easy to use portal for my homeserver. 
It aggregates different services and webapplications that I want easily accessible from within this portal.
The current state of the project is still very much a work in progress. 
Some functionality might not yet be fully decoupled from my own setup but the goal is to work toward this and have a generic solution for anyone.

Screenshots
-----------
#### Home screenshot
![Home](https://raw.githubusercontent.com/joszz/Chell-PHP-Portal/master/screenshots/home.png "Home")
#### Menu screenshot
![Menu](https://raw.githubusercontent.com/joszz/Chell-PHP-Portal/master/screenshots/menu.jpg "Menu")

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
Later in this project's development there will be a GUI provided for installation and also configuration of the portal items. 
For now this will have to be manually edited in the database. You can find information about the tables and associated columns below.

Next, copy over the source files to a directory on your server, preferrably (for security considerations) outside of the DocumentRoot directory of your webserver. The "app" directory of this project should NOT be accessible from the web. The "public" directory however, should be. This can be achieved with some Apache configuration, setting up an alias etc. I will not go into detail (for now) on how to achieve this.

Finally you can edit the config.ini file. You can find this file in "app/config/config.ini", the values to be specified speak for themselves.

Database documentation
----------------------
In this chapter you will find information about the database tables and associated columns.

#### devices
A table used to store the various devices in your network. Used for WOL and menu items which should only be active when a device is awake (pingable).

#### diskdrives
A table used to store the various local drives on the server. This will be used to display these drives and their available/total space.

#### menu_items
The different items/links in the collapsable menu. It is associated with a menu by the column parent_id (so multiple menus can be created). A menu_item can be dependent on a device to be disabled in the GUI when that device is not pingable.

#### menus
The available menus. For now only one menu will be displayed (ID 1, hardcoded for now).
