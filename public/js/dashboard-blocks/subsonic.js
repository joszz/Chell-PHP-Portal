(function ($) {
    $.fn.subsonic = function (options) {
        var settings = $.extend({
            block: $(this),
            url: $(this).data("url"),
            password: $(this).data("password"),
            username: $(this).data("username"),
            randomSongs: undefined
        }, options);

        settings.block.find(".glyphicon-refresh").off().on("click", function () {
            functions.nowPlaying();
            return false;
        });

        settings.block.find(".item").click(function () {
            return $.trim($(this).find(".title").html()) != "Nothing playing";
        });

        var functions = {
            nowPlaying: function (onload) {
                onload = typeof onload === 'undefined' ? false : onload;

                if (!onload) {
                    $(".subsonic").isLoading({
                        text: "Loading",
                        position: "overlay"
                    });
                }
                
                $.ajax({
                    url: functions.getURL("getNowPlaying"),
                    //todo: foreach entry
                    success: function (nowPlayingData) {
                        var entry = $(nowPlayingData).find("entry");

                        if (entry.length != 0) {
                            var url = functions.getURL("getCoverArt", { id: entry.attr("coverArt") });
                            var fancybox = settings.block.find("#subsonic_detail");
                            var duration = Math.floor(entry.attr("duration") / 60) + ":" + (entry.attr("duration") % 60);

                            settings.block.find(".item").css("background-image", "url(" + url + ")").attr("title", entry.attr("title"));
                            settings.block.find(".title").html(entry.attr("artist"));
                            settings.block.find(".subtitle").html(entry.attr("title"));
                            
                            fancybox.find("h4").html(entry.attr("artist"));
                            fancybox.find(".track").html(entry.attr("track"));
                            fancybox.find(".album").html(entry.attr("album"));
                            fancybox.find(".year").html(entry.attr("year"));
                            fancybox.find(".genre").html(entry.attr("genre"));
                            fancybox.find(".duration").html(duration);
                            fancybox.find(".bitrate").html(entry.attr("bitRate") + " kb/s");
                            fancybox.find(".playcount").html(entry.attr("playCount"));
                            fancybox.find(".lastplayed").html(entry.attr("minutesAgo") + " minutes ago");
                        }
                        else {
                            settings.block.find(".item").css("background-image", "url(img/icons/unknown.jpg)");
                            settings.block.find(".title").html("Nothing playing");
                        }
                    }
                });

                if (!onload) {
                    $(".subsonic").isLoading("hide");
                }
            },

            playNextRandom: function () {
                if (typeof settings.randomSongs == "undefined") {
                    $.ajax({
                        url: functions.getURL("getRandomSongs"),
                        success: function (randomSongs) {
                            settings.randomSongs = $(randomSongs);

                            settings.randomSongs.find("song").each(function () {

                            });
                        }
                    });
                }
            },

            setRandomSongs: function () {
                $.ajax({
                    url: functions.getURL("getRandomSongs"),
                    success: function (randomSongs) {
                        settings.randomSongs = $(randomSongs);
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

        functions.nowPlaying(true);

        return functions;
    }
})(jQuery);