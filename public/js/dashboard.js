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
    $(".toggle, .panel-heading h4").click(function () {
        var panel = $(this).closest(".panel");
        
        if (panel.find(".toggle:visible").length != 0) {
            panel.find(".toggle").toggleClass("glyphicon-minus glyphicon-plus");
            panel.find(".list-group, .panel-body").toggleClass("hidden-xs");
        }
    });

    $("footer .toggle-all").click(function () {
        var icon = $(this).find("span");

        $(".glyphicon-" + (icon.hasClass("glyphicon-resize-full") ? "plus" : "minus")).trigger("click");
        icon.toggleClass("glyphicon-resize-full glyphicon-resize-small");
    });
}

/**
* Shows a confirm dialog with yes/no buttons
* 
* @method openConfirmDialog
* @param title {String} The title to set for the confirm dialog.
* @param data {String} The data attributes to set on the confirm dialog, for later us.
* @param buttonClick {Object} callback for clicking the confirm button.
*/
function openConfirmDialog(title, data, buttonClick) {
    $("div#confirm-dialog h2").html(title);

    $.each(data, function (index, value) {
        $.each(value, function (index, value) {
            $("div#confirm-dialog").data(index, value);
        });
    });

    $("div#confirm-dialog button").off().on("click", buttonClick);

    $.fancybox({
        content: $("div#confirm-dialog").show(),
        modal: true
    });
}