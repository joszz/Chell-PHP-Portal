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
            baseUri: this.data("baseuri"),
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
            /**
            * Initializes the eventhandler for refresh button click and sets the interval to automatically refresh.
            * 
            * @method initialize
            */
            initialize: function () {
                if (settings.block.length === 0) {
                    return;
                }

                settings.block.on("click", ".fa-sync", function () {
                    clearInterval(settings.updateIntervalId);
                    settings.updateIntervalId = setInterval(function () {
                        functions.refreshImage();
                        functions.refreshModifiedTime();
                    }, settings.updateInterval);

                    functions.refreshImage();
                    functions.refreshModifiedTime();
                });

                settings.updateIntervalId = setInterval(function () {
                    functions.refreshImage();
                    functions.refreshModifiedTime();
                }, settings.updateInterval);
            },

            /**
            * Refreshes the image either by interval or manually by clicking the refresh button.
            * 
            * @method initialize
            */
            refreshImage: function () {
                var anchor = settings.block.find("a");
                var bgImgUrl = anchor.css("background-image");
                var bgImgUrlParts = bgImgUrl.split("?t=");

                anchor.css("background-image", bgImgUrlParts[0] + "?t=" + Date.now() + "\")");
            },

            refreshModifiedTime: function() {
                $.ajax({
                    url: settings.baseUri + "motion/modifiedTime",
                    success: function (data) {
                        settings.block.find(".subtitle").text(data);
                    }
                });
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);