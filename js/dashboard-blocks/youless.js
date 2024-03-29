"use strict";

/**
* The Youless widget.
*
* @class Youless
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
                    settings.updateIntervalId = setInterval(functions.update, settings.updateInterval);
                    functions.update();
                });

                settings.updateIntervalId = setInterval(functions.update, settings.updateInterval);
                functions.update(true);
            },

            /**
             * Updates the current statistics by calling the Youless controller.
             * 
             * @method update
             * @param {boolean} initialize  Whether called on initialization or not.
             */
            update: function (initialize) {
                if (settings.refreshing) {
                    return;
                }

                settings.refreshing = true;
                initialize = typeof initialize === "undefined" ? false : initialize;
                if (!initialize) {
                    settings.block.isLoading();
                }

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
                        settings.refreshing = false;
                    }
                });
            }

        };

        functions.initialize();
        return functions;
    };
})(jQuery);