"use strict";

var speedtest = false, opcache = false, pihole = false, snmp = false;

/**
* Main entry point for dashboard view.
*
* @class Index
* @module Dashboard
*/

initializeDashboardEventHandlers();
initializePlugins();

/**
 * Initializes all plugins for the dashboard.
 *
 * @method initializePlugins
 */
function initializePlugins() {
    if (typeof $.fn.devices !== "undefined") {
        $(".devices").devices();
    }
    if (typeof $.fn.sysinfo !== "undefined") {
        $(".sysinfo").sysinfo();
    }
    if (typeof $.fn.torrents !== "undefined") {
        $(".torrents").torrents();
    }
    if (typeof $.fn.gallery !== "undefined") {
        $(".movies, .episodes, .albums, .couchpotato, .jellyfin").gallery();
    }
    if (typeof $.fn.subsonic !== "undefined") {
        $(".subsonic").subsonic();
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
        opcache = $(".opcache").opcache();
    }
    if (typeof $.fn.youless !== "undefined") {
        $(".youless").youless();
    }
    if (typeof $.fn.pihole !== "undefined") {
        pihole = $(".pihole").pihole();
    }
    if (typeof $.fn.snmp !== "undefined") {
        snmp = $(".snmp").snmp();
    }
    if (typeof $.fn.verisure !== "undefined") {
        $(".verisure").verisure();
    }
    if (typeof $.fn.roborock !== "undefined") {
        $(".roborock").roborock();
    }
    if (typeof $.fn.cpu !== "undefined") {
        $(".cpu").cpu();
    }
    if (typeof $.fn.pulseway !== "undefined") {
        $(".pulseway").pulseway();
    }
    if (typeof $.fn.database_stats !== "undefined") {
        $(".database-stats").database_stats();
    }
    if (typeof $.fn.apache !== "undefined") {
        $(".apache").apache();
    }
    if (typeof $.fn.psaremote !== "undefined") {
        $(".psaremote").psaremote();
    }
    if (typeof $.fn.docker !== "undefined") {
        $(".docker").docker();
    }
    if (typeof $.fn.disks !== "undefined") {
        $(".disks").disks();
    }
    if (typeof $.fn.tdarr !== "undefined") {
        $(".tdarr").tdarr();
    }
    if (typeof $.fn.sonarr !== "undefined") {
        $(".sonarr").sonarr();
    }
    if (typeof $.fn.arr !== "undefined") {
        $(".arr").arr();
    }
    if (typeof $.fn.sonos !== "undefined") {
        $(".sonos").sonos();
    }
    if (typeof $.fn.adguard !== "undefined") {
        $(".adguard").adguard();
    }

    $(".time:not(.delayed)").each(function () {
        initializeTinyTimer($(this));
    });

    $.fancybox.defaults.smallBtn = $.fancybox.defaults.fullScreen = $.fancybox.defaults.slideShow = false;
    $.fancybox.defaults.buttons = ["close"];

    $("[data-nomaxwidth]").fancybox({
        beforeLoad: function () {
            this.opts.slideClass = "nomaxwidth";
        }
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
        $(".content .fa-" + ($(this).hasClass("fa-plus") ? "plus" : "minus")).trigger("click");
        $(this).toggleClass("fa-plus fa-minus");
    });

    $("#move_widgets").click(function () {
        $(".move-widget").toggleClass("hidden");
        sessionStorage.setItem("move-widget-visible", !$(".move-widget").hasClass("hidden"));

        return false;
    });

    if (sessionStorage.getItem("move-widget-visible") == "true") {
        $(".move-widget").removeClass("hidden");
    }

    setInterval(function () {
        isLoggedIn(() => { window.location.href = baseUri });
    }, 5000);
}

function initializeTinyTimer($this) {
    var date = new Date();
    date.setSeconds(date.getSeconds() - Math.floor($this.html()));
    $this.tinyTimer({ from: date, format: "%d days %0h:%0m:%0s" }).fadeIn();
}