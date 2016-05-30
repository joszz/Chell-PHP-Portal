(function ($) {
    $.fn.subsonic = function (options) {
        var settings = $.extend({
            block: $(this),
            url: $(this).data("url"),
            password: $(this).data("password"),
            username: $(this).data("username"),
        }, options);

        settings.block.find(".glyphicon-refresh").off().on("click", function () {
            functions.nowPlaying();
            return false;
        });

        var functions = {
            nowPlaying: function () {
                $.ajax({
                    url: functions.getURL("getNowPlaying"),
                    type: "POST",
                    success: function (nowPlayingData) {
                        var nowPlayingXML = $(nowPlayingData);
                        var entry = nowPlayingXML.find("entry");
                        var url = functions.getURL("getCoverArt", { id: entry.attr("coverArt") });

                        settings.block.find(".item").css("background-image", "url(" + url + ")");
                        settings.block.find(".title").html(entry.attr("artist"));
                        settings.block.find(".subtitle").html(entry.attr("title"));
                    }
                });
            },

            getSalt: function () {
                return Math.random().toString(36).substring(7);
            },

            getURL: function (view, arguments) {
                var salt = functions.getSalt();
                var password = md5(settings.password + salt);
                var url = settings.url + "rest/" + view + ".view?u=" + settings.username + "&t=" + password + "&s=" + salt + "&v=1.14.0&c=chell";

                $.each(arguments, function (key, value) {
                    url += "&" + key + "=" + value;
                });

                return url;
            }
        };

        functions.nowPlaying();

        return functions;
    }
})(jQuery);