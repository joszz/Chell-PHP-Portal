﻿$(function () {
    //Set focus to correct tab when URL navigated to with location.hash
    if (location.hash) {
        $("a[href='" + location.hash + "']").tab("show");
    }

    //This prevents weird jumpiness when a location.hash is present and is switched on document load (see above)
    $("ul.nav-tabs").fadeIn();
    $("div.tab-content").fadeIn();

    $(".nav-tabs a").click(function () {
        location.hash = $(this).attr("href");
    });

    $("form a.glyphicon-remove").click(function () {
        return (confirm("Are you sure you want to delete this item?"));
    });

    $('.nav-tabs').tabCollapse();
});