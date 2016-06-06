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
                        settings.block.find(".panel-heading button:not(.glyphicon-refresh)").addClass("disabled");
                    }
                    else {
                        settings.block.find(".panel-heading button:not(.glyphicon-refresh)").removeClass("disabled");
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
                                    var clone = settings.block.find(".player.nothing-playing").clone();
                                    var url = functions.subsonic.getURL("getCoverArt", { id: $(this).attr("coverArt") });
                                    var fancybox = clone.find("#nowplaying_detail").attr("id", "nowplaying_detail_" + index);
                                    var duration = Math.floor($(this).attr("duration") / 60) + ":" + ($(this).attr("duration") % 60);

                                    clone.removeClass("nothing-playing");
                                    clone.addClass("subsonic");

                                    clone.find(".item").css("background-image", "url(" + url + ")")
                                        .attr({ title: $(this).attr("title"), href: "#nowplaying_detail_" + index })
                                        .removeClass("disabled");

                                    clone.find(".title").html($(this).attr("artist"));
                                    clone.find(".subtitle").html($(this).attr("title"));

                                    fancybox.find("h4").html($(this).attr("artist"));
                                    fancybox.find(".track").html($(this).attr("track"));
                                    fancybox.find(".album").html($(this).attr("album"));
                                    fancybox.find(".year").html($(this).attr("year"));
                                    fancybox.find(".genre").html($(this).attr("genre"));
                                    fancybox.find(".duration").html(duration);
                                    fancybox.find(".bitrate").html($(this).attr("bitRate") + " kb/s");
                                    fancybox.find(".playcount").html($(this).attr("playCount"));
                                    fancybox.find(".lastplayed").html($(this).attr("minutesAgo") + " minutes ago");

                                    if (settings.block.find(".player:not(.nothing-playing):visible").length != 0) {
                                        clone.hide();
                                    }
                                    else {
                                        clone.show();
                                    }

                                    clone.appendTo(settings.block.find(".panel-body"));
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
                                var clone = settings.block.find(".player.nothing-playing").clone();
                                var imageURL = encodeURI(response.result.item.thumbnail);
                                imageURL = settings.kodi.url.replace("//", "//" + settings.kodi.username + ":" + settings.kodi.password + "@") + "/image/" + imageURL;

                                clone.removeClass("nothing-playing");
                                clone.addClass("kodi");

                                clone.find(".item").css("background-image", "url(" + imageURL + ")")
                                         .attr({ title: $(this).attr("title") })
                                         .removeClass("disabled");

                                clone.find(".title").html(response.result.item.artist);
                                clone.find(".subtitle").html(response.result.item.title);

                                if (settings.block.find(".player:not(.nothing-playing):visible").length != 0) {
                                    clone.hide();
                                }
                                else {
                                    clone.show();
                                }

                                clone.appendTo(settings.block.find(".panel-body"));
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