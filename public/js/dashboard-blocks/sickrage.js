﻿"use strict";

/**
* The gallery blocks on the dashboard.
* 
* @class Gallery
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.sickrage = function (options) {

        /**
        * All the settings for this block.
        * 
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            apiKey: this.data("sickrage-apikey"),
            baseUrl: this.data("sickrage-url"),
        }, options);

        /**
        * All the functions for this block.
        * 
        * @property functions
        * @type Object
        */
        var functions = {

            /**
            * Initializes the eventhandlers for button clicks to navigate between gallery items and sets the auto rotate interval for the gallery.
            * 
            * @method initialize
            */
            initialize: function () {
                settings.block.find(".fa-refresh").on("click", function () {
                    functions.refresh();
                });

                functions.refresh(true);
            },

            refresh: function (onload) {
                if (!onload) {
                    settings.block.isLoading();
                }

                $.ajax({
                    url: functions.url("future", "today|soon"),
                    dataType: "json",
                    success: function (data) {
                        data = data.data;

                        var ulToday = settings.block.find("#today ul");
                        var ulSoon = settings.block.find("#soon ul");

                        ulToday.find("li:not(.hidden)").remove();
                        ulSoon.find("li:not(.hidden)").remove();

                        $.each(data.today, function (index, value) {
                            functions.createItems(ulToday, value);
                        });

                        $.each(data.soon, function (index, value) {
                            functions.createItems(ulSoon, value);
                        });
                    },

                    complete: function () {
                        if (!onload) {
                            settings.block.isLoading("hide");
                        }
                    }
                });
            },

            createItems: function (ul, value) {
                var li = ul.find("li.hidden").clone();

                li.find("div").html("S" + zeropad(value.season, 2) + "E" + zeropad(value.episode, 2) + " | " + value.show_name);
                li.removeClass("hidden");
                li.prependTo(ul);
            },

            url: function (cmd, type) {
                return settings.baseUrl + "api/" + settings.apiKey + "/?cmd=" + cmd + "&type=" + type;
            }
        };

        functions.initialize();
    };
})(jQuery);