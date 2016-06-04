﻿$(function () {
    initializeDashboardEventHandlers();

    $(".sysinfo, .hardware, .harddisks, .processes").phpsysinfo();
    $(".transmission").transmission();
    $(".devices").devices().checkstates();
    $(".movies, .episodes, .albums").gallery();
    $(".nowplaying").nowplaying();

    var date = new Date();
    date.setSeconds(date.getSeconds() - Math.floor($("div.uptime").html()));
    $("div.uptime").tinyTimer({ from: date, format: "%d days %0h:%0m:%0s" });
});

function initializeDashboardEventHandlers() {
    $(".toggle, .panel-heading h4").click(function () {
        var panel = $(this).closest(".panel");
        
        if (panel.find(".toggle:visible").length != 0) {
            panel.find(".toggle").toggleClass("glyphicon-minus glyphicon-plus");
            panel.find(".list-group, .panel-body").slideToggle("fast");
        }
    });

    $("footer .toggle-all").click(function () {
        var icon = $(this).find("span");

        $(".glyphicon-" + (icon.hasClass("glyphicon-resize-full") ? "plus" : "minus")).trigger("click");
        icon.toggleClass("glyphicon-resize-full glyphicon-resize-small");
    });
}

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