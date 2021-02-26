"use strict";

var speedtest = false, opcache = false, pihole = false, snmp = false;

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
});

/**
 * Initializes all plugins for the dashboard.
 *
 * @method initializePlugins
 */
function initializePlugins() {
    $(".sysinfo, .hardware, .harddisks, .processes").phpsysinfo();
    $(".devices").devices();
    $(".transmission").transmission();
    $(".movies, .episodes, .albums, .couchpotato").gallery();
    $(".nowplaying").nowplaying();
    $(".sickrage").sickrage();
    $(".couchpotato").couchpotato();
    $(".motion").motion();
    speedtest = $(".speedtest").speedtest();
    opcache = $(".opcache").opcache();
    $(".youless").youless();
    pihole = $(".pihole").pihole();
    snmp = $(".snmp").snmp();
    $(".verisure").verisure();
    $(".roborock").roborock();

    $(".time:not(.delayed)").each(function () {
        initializeTinyTimer($(this));
    });
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

            if (panel.hasClass("gallery")) {
                panel.css("height", panel.find(".panel-body:eq(0)").hasClass("hidden-xs") ? "auto" : "379px");
            }
            else if (opcache !== false && panel.hasClass("opcache")) {
                opcache.initializeChart();
            }
            else if (pihole !== false && panel.hasClass("pihole")) {
                pihole.initializeChart();
            }
            else if (speedtest !== false && panel.hasClass("speedtest")) {
                speedtest.initUI();
            }

            if (!panel.find(".tab-content").length && !panel.find(".panel-body").length) {
                panel.find(".list-group").toggleClass("hidden-xs");
            }
        }
    });

    $("footer .toggle-all").click(function () {
        $(".fa-" + ($(this).hasClass("fa-expand") ? "plus" : "minus")).trigger("click");
        $(this).toggleClass("fa-expand fa-compress");
    });
}

function initializeTinyTimer($this) {
    var date = new Date();
    date.setSeconds(date.getSeconds() - Math.floor($this.html()));
    $this.tinyTimer({ from: date, format: "%d days %0h:%0m:%0s" }).fadeIn();
}