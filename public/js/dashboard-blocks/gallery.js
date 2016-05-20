(function ($) {
    $.fn.gallery = function (options) {
        var settings = $.extend({
            block: this,
            rotateInterval: this.data('rotate-interval') * 1000,
            rotateIntervalId: -1
        }, options);

        var functions = {
            rotateGallery: function (which, direction) {
                var parent = $("div." + which);
                var currentIndex = parent.find("div.item:visible").index();
                var offset = direction == "right" ? 1 : -1;

                nextIndex = parent.find("div.item:eq(" + (currentIndex + offset) + ")").length == 1 ? currentIndex + offset : 0;

                if (currentIndex != nextIndex) {
                    parent.find("div.item:eq(" + currentIndex + ")").fadeOut("fast", function () {
                        parent.find("div.item:eq(" + nextIndex + ")").fadeIn("fast");
                    });
                }
            }
        }

        settings.rotateIntervalId = setInterval(function () {
            rotateGallery(which, "right");
        }, settings.rotateInterval);

        $("div." + which + " .glyphicon-chevron-left, div." + which + " .glyphicon-chevron-right").click(function () {
            clearInterval(settings.rotateIntervalId);
            functions.rotateGallery(which, $(this).hasClass("glyphicon-chevron-left") ? "left" : "right");

            settings.rotateIntervalId = setInterval(function () {
                rotateGallery(which, "right");
            }, settings.rotateInterval);

            $(this).blur();
            return false;
        });

        return functions;
    }
})(jQuery);