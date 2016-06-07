(function ($) {
    $.fn.nowplaying = function (options) {
        var settings = $.extend({
            block: $(this),
            updateInterval: this.data("nowplaying-intveral") * 1000,
            updateIntervalId: -1,
            subsonic: {
                loading: false,
                url: $(this).data("subsonic-url"),
                password: $(this).data("subsonic-password"),
                username: $(this).data("subsonic-username"),
                timeout: 5000
            },
            kodi: {
                loading: false,
                url: $(this).data("kodi-url"),
                urlJSON: $(this).data("kodi-url") + "/jsonrpc",
                password: $(this).data("kodi-password"),
                username: $(this).data("kodi-username"),
                timeout: 5000
            },
        }, options);

        settings.block.find(".glyphicon-refresh").click(function () {
            functions.nowPlaying();
        });

        settings.block.find(".glyphicon-chevron-left, .glyphicon-chevron-right").click(function () {
            functions.rotate($(this).hasClass("glyphicon-chevron-left") ? "left" : "right");
        });

        var functions = {
            nowPlaying: function (onload) {
                onload = typeof onload === 'undefined' ? false : onload;

                if (!onload) {
                    $(".nowplaying").isLoading({
                        text: "Loading",
                        position: "overlay"
                    });
                }

                functions.setInterval();

                settings.block.find(".player:not(.nothing-playing)").remove();

                functions.subsonic.nowPlaying();
                functions.kodi.nowPlaying();

                var checkloadingInterval = window.setInterval(function () {
                    if (!onload && settings.kodi.loading == false && settings.subsonic.loading == false) {
                        $(".nowplaying").isLoading("hide");
                        clearInterval(checkloadingInterval);
                    }
                }, 100);
            },

            nowPlayingCallback: function () {
                if (settings.kodi.loading == false && settings.subsonic.loading == false) {
                    var playerCount = settings.block.find(".player:not(.nothing-playing)").length;

                    if (playerCount > 0) {
                        settings.block.find(".player.nothing-playing").hide();
                    }
                    else {
                        settings.block.find(".player.nothing-playing").show();
                    }

                    if (playerCount <= 1) {
                        settings.block.find(".panel-heading button.glyphicon-chevron-left, .panel-heading button.glyphicon-chevron-right").addClass("disabled");
                    }
                    else {
                        settings.block.find(".panel-heading button.glyphicon-chevron-left, .panel-heading button.glyphicon-chevron-right").removeClass("disabled");
                    }

                    var title = settings.block.find(".player:visible").hasClass("kodi") ? "Kodi" : "Subsonic";
                    settings.block.find("h4").html(title);
                }
            },

            setInterval: function () {
                clearInterval(settings.updateIntervalId);
                settings.updateIntervalId = setInterval(function () {
                    settings.block.find(".player:not(.nothing-playing)").remove();

                    functions.subsonic.nowPlaying();
                    functions.kodi.nowPlaying();
                }, settings.updateInterval);
            },

            rotate: function (direction) {
                var currentIndex = settings.block.find(".player:visible").index();
                var offset = direction == "right" ? 1 : -1;
                var nextIndex = 1;
                var nextBlock = settings.block.find(".player:eq(" + (currentIndex + offset) + ")");

                if (nextBlock.length == 1 && !nextBlock.hasClass("nothing-playing")) {
                    nextIndex = currentIndex + offset;
                }
                else if (nextBlock.hasClass("nothing-playing")) {
                    nextIndex = settings.block.find(".player").length - 1;
                }

                if (currentIndex != nextIndex) {
                    settings.block.find(".player:eq(" + currentIndex + ")").fadeOut("fast", function () {
                        settings.block.find(".player:eq(" + nextIndex + ")").fadeIn("fast", function () {
                            var title = settings.block.find(".player:visible").hasClass("kodi") ? "Kodi" : "Subsonic";
                            settings.block.find("h4").html(title);
                        }).css("display", "block");
                    });
                }
            },

            createPlayer: function (values) {
                var clone = settings.block.find(".player.nothing-playing").clone();
                var fancybox = clone.find("#nowplaying_detail").attr("id", "nowplaying_detail_" + values.index);

                clone.removeClass("nothing-playing");
                clone.addClass(values.type);

                clone.find(".item").css("background-image", "url(" + values.bgImage + ")")
                    .attr({ title: $(this).attr("title"), href: "#nowplaying_detail_" + values.index })
                    .removeClass("disabled");

                clone.find(".title").html(values.title);
                clone.find(".subtitle").html(values.subtitle);

                fancybox.find("h4").html(values.title);
                fancybox.find(".track").html(values.track);
                fancybox.find(".album").html(values.album);
                fancybox.find(".year").html(values.year);
                fancybox.find(".genre").html(values.genre);
                fancybox.find(".duration").html(values.duration);
                fancybox.find(".bitrate").html(values.bitRate);
                fancybox.find(".playcount").html(values.playCount);
                fancybox.find(".lastplayed").html(values.lastplayed);

                if (settings.block.find(".player:not(.nothing-playing):visible").length != 0) {
                    clone.hide();
                }
                else {
                    clone.show();
                }

                clone.appendTo(settings.block.find(".panel-body"));
            },

            subsonic: {
                nowPlaying: function () {
                    settings.subsonic.loading = true;

                    $.ajax({
                        url: functions.subsonic.getURL("getNowPlaying"),
                        timeout: settings.subsonic.timeout,
                        success: function (nowPlayingData) {
                            var entry = $(nowPlayingData).find("entry");

                            if (entry.length != 0) {
                                entry.each(function (index, value) {
                                    var bgImage = functions.subsonic.getURL("getCoverArt", { id: $(this).attr("coverArt") });
                                    var duration = Math.floor($(this).attr("duration") / 60) + ":" + ($(this).attr("duration") % 60);

                                    functions.createPlayer({
                                        type: "subsonic",
                                        index: index,
                                        bgImage: bgImage,
                                        title: $(this).attr("artist"),
                                        subtitle: $(this).attr("title"),
                                        track: $(this).attr("track"),
                                        album: $(this).attr("album"),
                                        genre: $(this).attr("genre"),
                                        duration: duration,
                                        bitrate: $(this).attr("bitRate") + " kb/s",
                                        playCount: $(this).attr("playCount"),
                                        lastPlayed: $(this).attr("minutesAgo") + " minutes ago"
                                    });
                                });
                            }
                        },
                        complete: function () {
                            settings.subsonic.loading = false;
                            functions.nowPlayingCallback();
                        }
                    });
                },

                getSalt: function () {
                    return Math.random().toString(36).substring(7);
                },

                getURL: function (view, arguments) {
                    var salt = functions.subsonic.getSalt();
                    var password = md5(settings.subsonic.password + salt);
                    var url = settings.subsonic.url + "rest/" + view + ".view?u=" + settings.subsonic.username + "&t=" + password + "&s=" + salt + "&v=1.14.0&c=chell";

                    $.each(arguments, function (key, value) {
                        url += "&" + key + "=" + value;
                    });

                    return url;
                },
            },

            kodi: {
                nowPlaying: function () {
                    settings.kodi.loading = true;

                    var data = {
                        id: 1,
                        jsonrpc: "2.0",
                        method: "Player.GetItem",
                        params: [0, ["title", "artist", "thumbnail"]]
                    };

                    $.ajax({
                        url: settings.kodi.urlJSON,
                        type: "POST",
                        contentType: "application/json",
                        data: JSON.stringify(data),
                        timeout: settings.kodi.timeout,
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('Authorization', 'Basic ' + btoa(settings.kodi.username + ':' + settings.kodi.password));
                        },
                        success: function (response) {
                            if ($.trim(response.result.item.title) != "") {
                                var bgImage = encodeURI(response.result.item.thumbnail);
                                bgImage = settings.kodi.url.replace("//", "//" + settings.kodi.username + ":" + settings.kodi.password + "@") + "/image/" + bgImage;

                                functions.createPlayer({
                                    type: "kodi",
                                    index: 99,
                                    bgImage: bgImage,
                                    title: response.result.item.artist,
                                    subtitle: response.result.item.title,
                                });
                            }
                        },
                        complete: function () {
                            settings.kodi.loading = false;
                            functions.nowPlayingCallback();
                        }
                    });
                },
            }
        };

        functions.nowPlaying(true);

        return functions;
    }
})(jQuery);