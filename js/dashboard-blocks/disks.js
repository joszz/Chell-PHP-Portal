﻿"use strict";

/**
 * The disks widgeton the dashboard.
 * 
 * @class Disks
 * @module Dashboard
 * @submodule DashboardBlocks
 * @example http://phpsysinfo.github.io/phpsysinfo/
 */
(function ($) {
    $.fn.disks = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: $(this),
            updateInterval: this.data("update-interval") * 1000,
            updateIntervalId: -1,
        }, options);

        /**
        * All the functions for this block.
        *
        * @property functions
        * @type Object
        */
        var functions = {

            /**
            * Initializes the eventhandlers for various actions on the widget. Calls update to retrieve the content of the widget.
            *
            * @method initialize
            */
            initialize: function () {
                settings.block.find(".fa-rotate").click(function () { functions.update(false); });

                functions.update(true);
            },

            /**
             * Updates the current statistics by calling the Disks controller.
             * 
             * @method update
             * @param {boolean} initialize  Whether called on initialization or not.
             */
            update: function (initialize) {
                initialize = typeof initialize === "undefined" ? false : initialize;
                if (!initialize) {
                    settings.block.isLoading();
                    clearInterval(settings.updateIntervalId);
                }

                $.ajax({
                    url: "disks",
                    dataType: "json",
                    success: function (disks) {
                        $.each(disks, function (index, value) {
                            var disk = settings.block.find("li:not(.clone):eq(" + index +")");

                            if (disk.length === 0) {
                                disk = settings.block.find("li.clone").clone();
                                disk.removeClass("hidden clone");
                                disk.addClass(index);
                                disk.appendTo(settings.block.find("ul"));
                            }

                            var percent = value.usage;
                            var total = getFormattedSize(value.size, 1);
                            var free = getFormattedSize(value.available, 1);
                            var used = getFormattedSize(value.used, 1);

                            disk.find(".name").html(value.mount).addClass("bs-tooltip").attr("title", value.mount);
                            disk.find(".progress-bar").css("width", percent);
                            disk.find(".percent").html(percent);
                            disk.find(".progress").addClass("bs-tooltip").attr("title", "Total: " + total + "\nFree: " + free + "\nUsed: " + used);

                            if (value.usage_percentage > 90) {
                                disk.find(".progress-bar").addClass("progress-bar-danger");
                            }
                            else if (value.usage_percentage > 70) {
                                disk.find(".progress-bar").addClass("progress-bar-warning");
                            }
                            else if (value.usage_percentage > 50) {
                                disk.find(".progress-bar").addClass("progress-bar-info");
                            }
                            else {
                                disk.find(".progress-bar").addClass("progress-bar-success");
                            }
                        });
                    },
                    complete: function () {
                        settings.updateIntervalId = setInterval(functions.update, settings.updateInterval);
                        settings.block.isLoading("hide");
                    }
                });
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);