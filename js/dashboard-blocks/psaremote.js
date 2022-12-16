"use strict";

/**
* The PSA remote block on the dashboard.
*
* @class Youless
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.psaremote = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            updateInterval: this.data("updateinterval") * 1000,
            updateIntervalId: -1,
            refreshing: false
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
                settings.block.on("click", ".fa-rotate", function () {
                    clearInterval(settings.updateIntervalId);
                    settings.updateIntervalId = setInterval(function () {
                        functions.update(false, true);
                    }, settings.updateInterval);
                    functions.update(false, false);
                });

                settings.updateIntervalId = setInterval(function () {
                    functions.update(false, true);
                }, settings.updateInterval);
                functions.update(true, true);
            },

            update: function (initialize, cache) {
                if (settings.refreshing) {
                    return;
                }

                settings.refreshing = true;
                initialize = typeof initialize === "undefined" ? false : initialize;
                if (!initialize) {
                    settings.block.isLoading();
                }

                $.ajax({
                    url: "psaremote/index/" + cache,
                    dataType: "json",
                    success: function (data) {
                        var tinyTimer = settings.block.find(".charging_remaining_time .value").data("tinyTimer");
                        if (tinyTimer) {
                            clearInterval(tinyTimer.interval);
                        }

                        settings.block.find(".last_refresh .value").text(data.energy.updated_at);
                        settings.block.find(".battery .value").text(data.energy.level + "%");
                        settings.block.find(".mileage .value").text(data.timed_odometer.mileage + " km");
                        settings.block.find(".charging_mode .value").text(data.energy.charging.charging_mode);

                        if (data.energy.charging.charging_mode !== "No") {
                            var date = new Date();
                            date.setSeconds(date.getSeconds() + data.energy.charging.remaining_time);

                            settings.block.find(".charging_rate, .charging_remaining_time").show();
                            settings.block.find(".charging_rate .value").text(data.energy.charging.charging_rate + " km/h");
                            settings.block.find(".charging_remaining_time .value").tinyTimer({ to: date });
                        }
                        else {
                            settings.block.find(".charging_rate, .charging_remaining_time").hide();
                        }
                    },
                    complete: function () {
                        settings.block.isLoading("hide");
                        settings.refreshing = false;
                    }
                });
            }

        };

        functions.initialize();
        return functions;
    };
})(jQuery);