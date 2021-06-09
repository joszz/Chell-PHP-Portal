"use strict";

/**
* The roborock widget.
*
* @class Roborock
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.database_stats = function (options) {
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
                    url: "databaseStats",
                    dataType: "json",
                    success: function (data) {
                        settings.block.find(".uptime").text(data.uptime);
                        settings.block.find(".threads").text(data.threads);
                        settings.block.find(".questions").text(data.questions);
                        settings.block.find(".slow-queries").text(data.slow_queries);
                        settings.block.find(".opens").text(data.opens);
                        settings.block.find(".flush-tables").text(data.flush_tables);
                        settings.block.find(".open-tables").text(data.open_tables);
                        settings.block.find(".queries-per-second").text(data.queries_per_second_avg);
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