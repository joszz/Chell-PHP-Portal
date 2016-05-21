(function ($) {
    $.fn.gallery = function (options) {
        this.each(function () {
            var settings = $.extend({
                block: $(this),
                rotateInterval: $(this).data('rotate-interval') * 1000,
                rotateIntervalId: -1
            }, options);

            var functions = {
                rotateGallery: function (direction) {
                    var currentIndex = settings.block.find("div.item:visible").index();
                    var offset = direction == "right" ? 1 : -1;

                    nextIndex = settings.block.find("div.item:eq(" + (currentIndex + offset) + ")").length == 1 ? currentIndex + offset : 0;

                    if (currentIndex != nextIndex) {
                        settings.block.find("div.item:eq(" + currentIndex + ")").fadeOut("fast", function () {
                            settings.block.find("div.item:eq(" + nextIndex + ")").fadeIn("fast");
                        });
                    }
                },
            }

            settings.rotateIntervalId = setInterval(function () {
                functions.rotateGallery("right");
            }, settings.rotateInterval);

            $(this).find(".glyphicon-chevron-left, .glyphicon-chevron-right").click(function () {
                clearInterval(settings.rotateIntervalId);
                functions.rotateGallery($(this).hasClass("glyphicon-chevron-left") ? "left" : "right");

                settings.rotateIntervalId = setInterval(function () {
                    rotateGallery("right");
                }, settings.rotateInterval);

                $(this).blur();
                return false;
            });

            //Preload images
            /*
            this.find(".item").each(function (index, value) {
                $("<img />").attr("src", $(this).css("background-image"));
            });
            */

            return functions;
        });
    }
})(jQuery);