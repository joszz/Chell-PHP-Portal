"use strict";

/**
* The Youless block on the dashboard.
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
                if (settings.block.length === 0) {
                    return;
                }

                settings.block.on("click", ".fa-sync", function () {
                    clearInterval(settings.updateIntervalId);
                    settings.updateIntervalId = setInterval(function () {
                        functions.refresh(false, true);
                    }, settings.updateInterval);
                    functions.refresh(false, false);
                });

                settings.updateIntervalId = setInterval(function () {
                    functions.refresh(false, true);
                }, settings.updateInterval);
                functions.refresh(true, true);
            },

            refresh: function (initialize, cache) {
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
                        settings.block.find(".last_refresh .value").text(data.energy.updated_at);
                        settings.block.find(".battery .value").text(data.energy.level + "%");
                        settings.block.find(".charging_mode .value").text(data.energy.charging.charging_mode);
                        settings.block.find(".charging_rate .value").text(data.energy.charging.charging_rate);
                        settings.block.find(".charging_plugged .value").text(data.energy.charging.plugged);
                        settings.block.find(".charging_remaining_time .value").text(data.energy.charging.remaining_time);
                        settings.block.find(".charging_status .value").text(data.energy.charging.status);
                        settings.block.find(".mileage .value").text(data.timed_odometer.mileage);
                        settings.block.find(".acceleration .value").text(data.kinetic.acceleration);
                        settings.block.find(".moving .value").text(data.kinetic.moving);
                        settings.block.find(".pace .value").text(data.kinetic.pace);
                        settings.block.find(".speed .value").text(data.kinetic.speed);
                        settings.block.find(".doors_state .value").text(data.doors_state);
                        settings.block.find(".battery_consumption .value").text(data.energy.consumption);
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