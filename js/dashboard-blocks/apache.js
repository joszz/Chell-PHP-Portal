"use strict";

/**
* The roborock widget.
*
* @class Roborock
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.apache = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: $(this),
            updateInterval: $(this).data("update-interval") * 1000,
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
                settings.block.find(".fa-sync").click(function () { functions.update(false); });

                functions.update(true);
            },

            /**
             * Updates the current statistics by calling the Roborock controller.
             * 
             * @method update
             * @param {boolean} initialize  Whether called on initialization or not.
             */
            update: function (initialize) {
                initialize = typeof initialize === "undefined" ? false : initialize;
                if (!initialize) {
                    settings.block.isLoading();
                    window.clearInterval(settings.updateIntervalId);
                }

                $.ajax({
                    url: "apache",
                    dataType: "json",
                    success: function (data) {
                        settings.block.find(".version").text(data.ServerVersion);
                        settings.block.find(".built").text(data['Server Built']);
                        settings.block.find(".load").text(data.CPULoad);
                        settings.block.find(".requests-s").text(data.ReqPerSec);
                        settings.block.find(".bytes-s").text(data.BytesPerSec);
                        settings.block.find(".bytes-requests").text(data.BytesPerReq);
                        settings.block.find(".duration-request").text(data.DurationPerReq);

                        var date = new Date();
                        date.setSeconds(date.getSeconds() - Math.floor(data.ServerUptimeSeconds));

                        if (settings.block.find("div.time").data("tinyTimer") !== undefined) {
                            clearInterval(settings.block.find("div.time").data("tinyTimer").interval);
                        }
                        settings.block.find("div.time").tinyTimer({ from: date, format: "%d days %0h:%0m:%0s" });
                    },
                    complete: function () {
                        settings.updateIntervalId = window.setInterval(functions.update, settings.updateInterval);
                        settings.block.isLoading("hide");
                    }
                });
            },
        };

        functions.initialize();

        return functions;
    };
})(jQuery);