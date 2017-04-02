/**
* Main entry point for dashboard view.
* 
* @class Index
* @module Dashboard
*/

/**
* Document onload, call to initialize dashboardblocks, eventhandlers and plugins.
* 
* @method document.onload
*/
$(function () {
    initializeDashboardEventHandlers();

    $(".sysinfo, .hardware, .harddisks, .processes").phpsysinfo();
    $(".transmission").transmission();
    $(".devices").devices();
    $(".movies, .episodes, .albums").gallery();
    $(".nowplaying").nowplaying();

    var date = new Date();
    date.setSeconds(date.getSeconds() - Math.floor($("div.uptime").html()));
    $("div.uptime").tinyTimer({ from: date, format: "%d days %0h:%0m:%0s" });
});

/**
* Initializes the eventhandlers
* 
* @method initializeDashboardEventHandlers
*/
function initializeDashboardEventHandlers() {
    $(".toggle-collapse, .panel-heading h4").click(function () {
        var panel = $(this).closest(".panel");
        
        if (panel.find(".toggle-collapse:visible").length != 0) {
            panel.find(".toggle-collapse").toggleClass("fa-minus fa-plus");
            panel.find(".list-group, .panel-body").toggleClass("hidden-xs");
        }
    });

    $("footer .toggle-all").click(function () {
        var icon = $(this).find("span");

        $(".fa-" + (icon.hasClass("fa-expand") ? "plus" : "minus")).trigger("click");
        icon.toggleClass("fa-expand fa-compress");
    });
}