"use strict";

/**
* The docker widget.
*
* @class Docker
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.docker = function (options) {
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
                settings.block.find(".fa-sync").click(function () { functions.update(false) });
                settings.updateIntervalId = setInterval(functions.update, settings.updateInterval);
                functions.update(true);
            },

            /**
             * Updates the current statistics by calling the Docker controller.
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
                    url: "docker",
                    dataType: "json",
                    success: function (data) {
                        settings.block.find(".docker-container:not(.hidden)").remove();
                        $.each(data, function (_index, container) {
                            var clone = settings.block.find(".docker-container.hidden").clone();
                            clone.find(".name span").text(container.name).attr("title", container.image);
                            clone.find(".status").text(container.status);
                            clone.removeClass("hidden");
                            clone.appendTo(settings.block.find("ul"));
                        });
                    },
                    complete: function () {
                        settings.updateIntervalId = window.setInterval(functions.update, settings.updateInterval);
                        settings.block.isLoading("hide");
                    }
                });
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);