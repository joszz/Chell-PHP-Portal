"use strict";

/**
* The gallery blocks on the dashboard.
* 
* @class Gallery
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.gallery = function (options) {
        this.each(function () {

            /**
            * All the settings for this block.
            * 
            * @property settings
            * @type Object
            */
            var settings = $.extend({
                block: $(this),
                rotateInterval: $(this).data('rotate-interval') * 1000,
                rotateIntervalId: -1
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
                    settings.rotateIntervalId = setInterval(function () {
                        functions.rotateGallery("right");
                    }, settings.rotateInterval);

                    settings.block.find(".fa-chevron-left, .fa-chevron-right").click(function () {
                        clearInterval(settings.rotateIntervalId);
                        functions.rotateGallery($(this).hasClass("fa-chevron-left") ? "left" : "right");

                        settings.rotateIntervalId = setInterval(function () {
                            functions.rotateGallery("right");
                        }, settings.rotateInterval);
                    });
                },

                /**
                * Called when navigating to a new item in the gallery. Either by button clicks or by the interval.
                * 
                * @method rotateGallery
                * @param {String} direction The direction to rotate to, valid values are "left" and "right".
                */
                rotateGallery: function (direction) {
                    var currentIndex = settings.block.find(".item:visible").index();
                    var offset = direction === "right" ? 1 : -1;
                    var nextIndex = settings.block.find(".item:eq(" + (currentIndex + offset) + ")").length === 1 ? currentIndex + offset : 0;

                    if (currentIndex !== nextIndex) {
                        settings.block.find(".item:eq(" + currentIndex + ")").fadeOut("fast", function () {
                            settings.block.find(".item:eq(" + nextIndex + ")").fadeIn("fast").css("display", "block");
                        });
                    }
                }
            };

            functions.initialize();
        });
    };
})(jQuery);