"use strict";

/**
* The transmission block on the dashboard.
* 
* @class Transmission
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.motion = function (options) {

        /**
        * All the settings for this block.
        * 
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            updateInterval: this.data("motion-interval") * 1000,
            updateIntervalId: -1
        }, options);

        /**
        * All the functions for this block.
        * 
        * @property functions
        * @type Object
        */
        var functions = {
            initialize: function () {
                settings.block.on("click", ".fa-sync", function () {
                    clearInterval(settings.updateIntervalId);
                    settings.updateIntervalId = setInterval(function () {
                        functions.refreshImage();
                    }, settings.updateInterval);

                    functions.refreshImage();
                });
            },

            refreshImage: function () {
                var anchor = settings.block.find("a");
                var bgImgUrl = anchor.css("background-image");
                var bgImgUrlParts = bgImgUrl.split("?t=");
                
                anchor.css("background-image", bgImgUrlParts[0] + "?t=" + Date.now() + "\")");
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);