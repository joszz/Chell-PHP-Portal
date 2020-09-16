"use strict";

/**
* The speedtest block on the dashboard.
*
* @class Speedtest
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.youless = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            updateInterval: this.data("updateinterval") * 1000,
            updateIntervalId: -1
        }, options);


        /**
        * All the functions for this block.
        *
        * @property functions
        * @type Object
        */
        var functions = {
            /**
            * Initializes the eventhandlers for the various button clicks.
            *
            * @method checkstates
            */
            initialize: function () {
                if (settings.block.length === 0) {
                    return;
                }

                settings.block.on("click", ".fa-sync", function () {
                    clearInterval(settings.updateIntervalId);
                    settings.updateIntervalId = setInterval(function () {
                        functions.refresh();
                    }, settings.updateInterval);

                    functions.refresh();
                });

                settings.updateIntervalId = setInterval(function () {
                    functions.refresh();
                }, settings.updateInterval);

                //use timeout to prevent isloading from positioning incorrectly on load
                window.setTimeout(functions.refresh, 0);
            },

            refresh: function () {
                settings.block.isLoading();

                $.ajax({
                    url: "youless/",
                    dataType: "json",
                    success: function (data) {
                        settings.block.find(".counter .value").text(data.counter);
                        settings.block.find(".power .value span").text(data.power);
                        settings.block.find(".power .value").attr("class", "value " + data.class);
                    },
                    complete: function () {
                        settings.block.isLoading("hide");
                    }
                });
            }

        };

        functions.initialize();
        return functions;
    };
})(jQuery);