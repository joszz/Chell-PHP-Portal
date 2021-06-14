"use strict";

(function ($) {
    $.fn.rcpu = function (options) {
        var settings = $.extend({
            block: $(this),
            iframe: $(this).find("iframe"),
        }, options);

        /**
        * All the functions for this block.
        *
        * @property functions
        * @type Object
        */
        var functions = {
            /**
            * Initializes the widget and listens for iframe load/resize events to set correct height of the widget.
            *
            * @method initialize
            */
            initialize: function () {
                settings.iframe = settings.block.find("iframe");

                settings.block.isLoading();
                settings.iframe.on("load", function () {
                    new ResizeObserver(functions.setHeight).observe(settings.iframe.contents().find("body")[0]);
                    settings.block.isLoading("hide");
                });
            },

            /**
             * Sets the height of the widget and makes it visible.
             * If the processes widget is set to the left, adjust it's height to match.
             * 
             * @method setHeight
             */
            setHeight: function () {
                var height = settings.iframe.contents().height();
                settings.block.find(".panel-body").height(height);
                settings.block.parent().prev().find(".processes ul").height(height + 15);

                window.setTimeout(functions.setVisibility, 0);
            },

            setVisibility: function () {
                settings.iframe.animate({ opacity: 1 }, "fast");
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);