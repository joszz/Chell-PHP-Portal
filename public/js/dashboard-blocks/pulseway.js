"use strict";

/**
* The roborock widget.
*
* @class Roborock
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.pulseway = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: $(this),
            systems: $(this).data("systems").split(","),
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
                settings.block.find(".fa-sync").click(function () { functions.update(false) });
                settings.updateIntervalId = setInterval(functions.update, settings.updateInterval);
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
                    url: "pulseway",
                    dataType: "json",
                    success: function (data) {
                        settings.block.find(".system:not(.hidden)").remove();
                        $.each(data, function (_index, system) {
                            if (system) {
                                var clone = settings.block.find(".system.hidden").clone();
                                clone.find(".name span").text(system.name).attr("title", system.description);
                                clone.find(".icon i").attr({
                                    class: "fa fa-power-off text-" + (system.is_online ? "success" : "danger"),
                                    title: system.is_online ? "Online" : "Offline"
                                });
                                clone.find(".value span.uptime").text(system.uptime);

                                if (system.uptime.indexOf("Offline") !== -1) {
                                    clone.find(".value span.uptimelabel").hide();
                                }

                                clone.removeClass("hidden");
                                clone.appendTo(settings.block.find("ul"));
                            }
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