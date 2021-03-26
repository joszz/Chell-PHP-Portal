"use strict";

(function ($) {
    $.fn.rcpu = function (options) {
        var settings = $.extend({
            block: $(this),
            iframe: null
        }, options);

        /**
        * All the functions for this block.
        *
        * @property functions
        * @type Object
        */
        var functions = {
            initialize: function () {
                settings.iframe = settings.block.find("iframe");

                if (!settings.block.find(".panel-body").is(":hidden")) {
                    settings.block.isLoading();
                    settings.iframe.ready(function () {
                        setTimeout(functions.setHeight, 1000);
                    });
                }
                else {
                    settings.block.find(".toggle-collapse, .panel-heading h4").click(function () {
                        functions.setHeight();
                    });
                }
            },

            setHeight: function () {
                var height = settings.iframe.contents().height();
                settings.block.find(".panel-body").height(height);
                settings.block.parent().prev().find(".processes ul").height(height + 15);
                settings.iframe.css("visibility", "visible");
                settings.block.isLoading("hide");
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);