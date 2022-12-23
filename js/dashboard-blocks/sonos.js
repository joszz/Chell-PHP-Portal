"use strict";

/**
* The Sonos widget.
*
* @class Sonos
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.sonos = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: $(this),
            updateInterval: this.data("updateinterval") * 1000,
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
                settings.block.find(".fa-rotate").off().on("click", function () {
                    functions.update(false);
                });

                settings.updateIntervalId = setInterval(function () {
                    functions.update(false);
                }, settings.updateInterval);
                functions.update(true);
            },

            /**
             * Updates the current statistics by calling the Sonos controller.
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
                    url: "sonos",
                    dataType: "json",
                    success: function (data) {
                        settings.block.find(".title").text(data.track).fadeIn("fast");
                        settings.block.find(".subtitle").text(data.artist).fadeIn("fast");
                        settings.block.find("img").attr("src", data.image ? "sonos/image?url=" + data.image : "img/icons/unknown.jpg");
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