"use strict";

/**
* The gallery blocks on the dashboard.
*
* @class Gallery
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.snmp = function (options) {
        this.each(function () {

            /**
            * All the settings for this block.
            *
            * @property settings
            * @type Object
            */
            var settings = $.extend({
                block: $(this),
            }, options);

            /**
            * All the functions for this block.
            *
            * @property functions
            * @type Object
            */
            var functions = {

                /**
                * Initializes the eventhandlers for button clicks to navigate between gallery items and sets the auto rotate interval for the gallery.
                *
                * @method initialize
                */
                initialize: function () {
                    settings.block.find(".fa-chevron-left, .fa-chevron-right").click(function () {
                        var offset = $(this).hasClass("fa-chevron-right") ? 1 : -1;
                        var currentHost = settings.block.find(".host:visible");
                        var currentIndex = currentHost.index();

                        var nextHost = settings.block.find(".host:eq(" + (currentIndex + offset) + ")");
                        var nextIndex = nextHost.length === 1 ? currentIndex + offset : 0;
                        nextHost = settings.block.find(".host:eq(" + nextIndex + ")");

                        if (currentIndex !== nextIndex) {
                            currentHost.fadeOut("fast", function () {
                                settings.block.find("h4").html(nextHost.data("name"));
                                nextHost.fadeIn("fast").css("display", "block");
                            });
                        }
                    });
                },
            };

            functions.initialize();
        });
    };
})(jQuery);