﻿/**
* Main entry point for settings view.
* 
* @class Index
* @module Settings
*/

/**
* Document onload, sets up setting view specific eventhandlers.
* 
* @method document.onload
*/
$(function () {
    $('.nav-tabs').tabCollapse();

    //Set focus to correct tab when URL navigated to with location.hash
    if (location.hash) {
        $("a[href='" + location.hash + "']").tab("show");
    }

    $(".nav-tabs a").click(function () {
        location.hash = $(this).attr("href");
    });

    $(".fa-remove").click(function () {
        return (confirm("Are you sure you want to delete this item?"));
    });

    $("input[type='number']").TouchSpin({
        verticalbuttons: true,
        verticalupclass: 'fa fa-chevron-up',
        verticaldownclass: 'fa fa-chevron-down'
    });
});