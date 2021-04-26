"use strict";

/**
* The roborock widget.
*
* @class Roborock
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.roborock = function (options) {
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
                settings.block.find(".start-stop").click(functions.start_stop);

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
                    url: "roborock",
                    dataType: "json",
                    success: function (data) {
                        settings.block.find(".state").html(data.state);
                        settings.block.find(".battery").html(data.battery + "%");
                        settings.block.find(".fan").html(data.fan + "%");
                        settings.block.find(".area").html(data.area + " m<sup>2</sup>");
                        var cleaningTime = settings.block.find(".cleaningtime");

                        if (data.state == "Cleaning") {
                            if (cleaningTime.data("tinyTimer") !== undefined) {
                                clearInterval(cleaningTime.data("tinyTimer").interval);
                            }

                            var parts = data.time.split(':');
                            cleaningTime.html(parseInt(parts[0]) * 3600 + parseInt(parts[1]) * 60 + parseInt(parts[2]));
                            initializeTinyTimer(cleaningTime);

                            settings.block.find(".start-stop").removeClass("fa-play").addClass("fa-stop");
                        }
                        else {
                            cleaningTime.html(data.time);
                            settings.block.find(".start-stop").removeClass("fa-stop").addClass("fa-play");
                        }
                    },
                    complete: function () {
                        settings.updateIntervalId = window.setInterval(functions.update, settings.updateInterval);
                        settings.block.isLoading("hide");
                    }
                });
            },

            /**
             * Starts or stops Roborock, depending on current state.
             * 
             * @method start_stop
             */
            start_stop: function () {
                var start = $(this).hasClass("fa-play");
                settings.block.isLoading();
                $.ajax({
                    url: "roborock/" + (start ? "start" : "stop"),
                    success: function () {
                        showAlert("success", "Roborock " + (start ? "started" : "stopped") + " cleaning");
                    },
                    complete: function () {
                        settings.block.isLoading("hide");
                        functions.update();
                    }
                });
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);