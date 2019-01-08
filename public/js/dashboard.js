"use strict";

var speedtest = false, opache = false;

/**
* Main entry point for dashboard view.
* 
* @class Index
* @module Dashboard
*/

/**
* Document onload, call to initialize eventhandlers and plugins.
* 
* @method document.onload
*/
$(function () {
    initializeDashboardEventHandlers();
    initializePlugins();

    if ($("html").hasClass("time")) {
        getLocation();
    }
});

/**
 * Initializes all plugins for the dashboard.
 * 
 * @method initializePlugins
 */
function initializePlugins() {
    $(".sysinfo, .hardware, .harddisks, .processes").phpsysinfo();
    $(".devices").devices();

    if (typeof $.fn.transmission !== "undefined") {
        $(".transmission").transmission();
    }
    if (typeof $.fn.gallery !== "undefined") {
        $(".movies, .episodes, .albums, .couchpotato").gallery();
    }
    if (typeof $.fn.nowplaying !== "undefined") {
        $(".nowplaying").nowplaying();
    }
    if (typeof $.fn.sickrage !== "undefined") {
        $(".sickrage").sickrage();
    }
    if (typeof $.fn.couchpotato !== "undefined") {
        $(".couchpotato").couchpotato();
    }
    if (typeof $.fn.motion !== "undefined") {
        $(".motion").motion();
    }
    if (typeof $.fn.speedtest !== "undefined") {
        speedtest = $(".speedtest").speedtest();
    }
    if (typeof $.fn.opcache !== "undefined") {
        opache = $(".opcache").opcache();
    }

    var date = new Date();
    date.setSeconds(date.getSeconds() - Math.floor($("div.uptime").html()));
    $("div.uptime").tinyTimer({ from: date, format: "%d days %0h:%0m:%0s" });
}

/**
* Initializes the eventhandlers
* 
* @method initializeDashboardEventHandlers
*/
function initializeDashboardEventHandlers() {
    $(".toggle-collapse, .panel-heading h4").click(function () {
        var panel = $(this).closest(".panel");

        if (panel.find(".toggle-collapse:visible").length !== 0) {
            panel.find(".toggle-collapse").toggleClass("fa-minus fa-plus");
            panel.find(".panel-body:eq(0)").toggleClass("hidden-xs");

            if (panel.hasClass("gallery")){
                panel.css("height", panel.find(".panel-body:eq(0)").hasClass("hidden-xs") ? "auto" : "379px");
            }

            if (panel.hasClass("opcache")) {
                opache.initializeChart();
            }


            if (!panel.find(".tab-content").length) {
                panel.find(".list-group").toggleClass("hidden-xs");
            }
        }
    });

    $("footer .toggle-all").click(function () {
        $(".fa-" + ($(this).hasClass("fa-expand") ? "plus" : "minus")).trigger("click");
        
        if ($(this).hasClass("fa-expand") && speedtest !== false) {
            speedtest.initUI();
        }

        $(this).toggleClass("fa-expand fa-compress");
    });
}