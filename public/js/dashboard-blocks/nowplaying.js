"use strict";

/**
* The nowplaying block on the dashboard.
* 
* @class Nowplaying
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.nowplaying = function (options) {

        /**
        * All the settings for this block.
        * 
        * @property settings
        * @type Object
        */
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
            }
        }, options);

        /**
        * All the functions for this block.
        * 
        * @property functions
        * @type Object
        */
        var functions = {

            /**
            * Initializes the eventhandlers for button clicks to navigate between nowplaying  items and sets the refresh button click handler.
            * 
            * @method initialize
            */
            initialize: function () {
                settings.block.find(".fa-sync").click(function () {
                    functions.nowPlaying();
                });

                settings.block.find(".fa-chevron-left, .fa-chevron-right").click(function () {
                    functions.rotate($(this).hasClass("fa-chevron-left") ? "left" : "right");
                });

                functions.nowPlaying(true);
            },

            /**
            * Wrapper function to retrieve all nowplaying details. 
            * 
            * @method nowPlaying
            * @param {Boolean} onload Whether this function is called as part of initialization
            */
            nowPlaying: function (onload) {
                onload = typeof onload === "undefined" ? false : onload;

                if (!onload) {
                    $(".nowplaying").isLoading();
                }

                functions.setInterval();

                settings.block.find(".panel-heading button.fa-chevron-left, .panel-heading button.fa-chevron-right").addClass("disabled");
                settings.block.find(".player.nothing-playing").hide();
                settings.block.find(".player:not(.nothing-playing)").remove();

                functions.subsonic.nowPlaying();
                functions.kodi.nowPlaying();

                var checkloadingInterval = window.setInterval(function () {
                    if (!onload && settings.kodi.loading === false && settings.subsonic.loading === false) {
                        $(".nowplaying").isLoading("hide");
                        clearInterval(checkloadingInterval);
                    }
                }, 100);
            },

            /**
            * Called on completing AJAX request to retrieve nowplaying details for either Kodi or Subsonic.<br />
            * Hides and shows certain DOM elements based on what's playing.
            * 
            * @method nowPlayingCallback
            */
            nowPlayingCallback: function () {
                if (settings.kodi.loading === false && settings.subsonic.loading === false) {
                    var playerCount = settings.block.find(".player:not(.nothing-playing)").length;

                    if (playerCount > 0) {
                        settings.block.find(".player.nothing-playing").hide();
                    }
                    else {
                        settings.block.find(".player.nothing-playing").show();
                    }

                    if (playerCount <= 1) {
                        settings.block.find(".panel-heading button.fa-chevron-left, .panel-heading button.fa-chevron-right").addClass("disabled");
                    }
                    else {
                        settings.block.find(".panel-heading button.fa-chevron-left, .panel-heading button.fa-chevron-right").removeClass("disabled");
                    }

                    var title = settings.block.find(".player:visible").hasClass("kodi") ? "Kodi" : "Subsonic";
                    settings.block.find("h4").html(title);
                }
            },

            /**
            * Sets the interval to retreive new nowplaying information automatically.
            * 
            * @method setInterval
            */
            setInterval: function () {
                clearInterval(settings.updateIntervalId);
                settings.updateIntervalId = setInterval(function () {
                    settings.block.find(".player:not(.nothing-playing)").remove();

                    functions.subsonic.nowPlaying();
                    functions.kodi.nowPlaying();
                }, settings.updateInterval);
            },

            /**
            * Rotates the block to the next/prev player. Called by interval or pressing next/prev buttons.
            * 
            * @method rotate
            * @param {String} direction Which direction to rotate to. Valid values are "left" and "right".
            */
            rotate: function (direction) {
                var currentIndex = settings.block.find(".player:visible").index();
                var offset = direction === "right" ? 1 : -1;
                var nextIndex = 1;
                var nextBlock = settings.block.find(".player:eq(" + (currentIndex + offset) + ")");

                if (nextBlock.length === 1 && !nextBlock.hasClass("nothing-playing")) {
                    nextIndex = currentIndex + offset;
                }
                else if (nextBlock.hasClass("nothing-playing")) {
                    nextIndex = settings.block.find(".player").length - 1;
                }

                if (currentIndex !== nextIndex) {
                    settings.block.find(".player:eq(" + currentIndex + ")").fadeOut("fast", function () {
                        settings.block.find(".player:eq(" + nextIndex + ")").fadeIn("fast", function () {
                            var title = settings.block.find(".player:visible").hasClass("kodi") ? "Kodi" : "Subsonic";
                            settings.block.find("h4").html(title);
                        }).css("display", "block");
                    });
                }
            },

            /**
            * Clones the .player.nothing-playing and uses it to set up a new player based on the supplied values.
            * 
            * @method createPlayer
            * @param {Object} values The values to set for the new player.
            */
            createPlayer: function (values) {
                var clone = settings.block.find(".player.nothing-playing").clone();
                var fancybox = clone.find("#nowplaying_detail").attr("id", "nowplaying_detail_" + values.index);

                clone.removeClass("nothing-playing");
                clone.addClass(values.type);

                clone.find(".item > img").attr("src", values.bgImage)
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
                fancybox.find(".bitrate").html(values.bitRate) + " kb/s";
                fancybox.find(".playcount").html(values.playCount);
                fancybox.find(".lastplayed").html(values.lastplayed + " minutes ago");

                if (settings.block.find(".player:not(.nothing-playing):visible").length !== 0) {
                    clone.hide();
                }
                else {
                    clone.show();
                }

                clone.appendTo(settings.block.find(".panel-body"));
            },

            /**
            * The Subsonic object containing all the functions related to Subsonic nowplaying.
            * 
            * @property subsonic
            * @type Object
            * @example http://www.subsonic.org/pages/api.jsp
            */
            subsonic: {

                /**
                * Retrieves the nowplaying information from SubSonic. On complete calls functions.nowPlayingCallback().
                * 
                * @method subsonic.nowPlaying
                */
                nowPlaying: function () {
                    settings.subsonic.loading = true;

                    $.ajax({
                        url: functions.subsonic.getURL("getNowPlaying"),
                        timeout: settings.subsonic.timeout,
                        success: function (nowPlayingData) {
                            var entry = $(nowPlayingData).find("entry");

                            if (entry.length !== 0) {
                                entry.each(function (index, value) {
                                    var bgImage = functions.subsonic.getURL("getCoverArt", { id: $(this).attr("coverArt") });
                                    var duration = Math.floor($(this).attr("duration") / 60) + ":" + $(this).attr("duration") % 60;

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
                                        bitrate: $(this).attr("bitRate"),
                                        playCount: $(this).attr("playCount"),
                                        lastPlayed: $(this).attr("minutesAgo")
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


                /**
                * Creates a salt, used to salt the login information with. Used by functions.subsonic.getURL.
                * 
                * @method subsonic.getSalt
                * @returns {String} A random string used as salt.
                */
                getSalt: function () {
                    return Math.random().toString(36).substring(7);
                },

                /**
                * Creates a SubSonic style REST URL with username, password and salt.<br />
                * Pass along an Array of arguments to append them to the URL.
                * 
                * @method subsonic.getURL
                * @param {String}       view Which SubSonic view to retrieve.
                * @param {Array} args   The extra arguments to append to the REST URL. The key will be used as the queryparameter, the value as queryvalue.
                * @returns {String}     The URL to call Subsonic by.
                */
                getURL: function (view, args) {
                    var salt = functions.subsonic.getSalt();
                    var password = md5(settings.subsonic.password + salt);
                    var url = settings.subsonic.url + "rest/" + view + ".view?u=" + settings.subsonic.username + "&t=" + password + "&s=" + salt + "&v=1.14.0&c=chell";

                    $.each(args, function (key, value) {
                        url += "&" + key + "=" + value;
                    });

                    return url;
                }
            },

            /**
            * The Kodi object containing all the functions related to Kodi nowplaying.
            * 
            * @property kodi
            * @type Object
            * @example http://kodi.wiki/view/JSON-RPC_API
            * @example http://kodi.wiki/view/JSON-RPC_API/Examples
            * @example http://forum.kodi.tv/showthread.php?tid=157996
            */
            kodi: {
                /**
                * Retrieves the nowplaying information from Kodi. On complete calls functions.nowPlayingCallback().
                * 
                * @method kodi.nowPlaying
                */
                nowPlaying: function () {
                    settings.kodi.loading = true;

                    var data = {
                        id: 1,
                        jsonrpc: "2.0",
                        method: "Player.GetActivePlayers"
                    };

                    $.ajax({
                        url: settings.kodi.urlJSON,
                        type: "POST",
                        contentType: "application/json",
                        data: JSON.stringify(data),
                        timeout: settings.kodi.timeout,
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader("Authorization", "Basic " + btoa(settings.kodi.username + ":" + settings.kodi.password));
                        },
                        success: function (response) {
                            if (response.result[0]) {
                                var playerID = response.result[0].playerid;

                                var data = {
                                    id: 1,
                                    jsonrpc: "2.0",
                                    method: "Player.GetItem",
                                    params: {
                                        properties: ["title", "artist", "thumbnail"],
                                        playerid: playerID
                                    }
                                };

                                $.ajax({
                                    url: settings.kodi.urlJSON,
                                    type: "POST",
                                    contentType: "application/json",
                                    data: JSON.stringify(data),
                                    timeout: settings.kodi.timeout,
                                    beforeSend: function (xhr) {
                                        xhr.setRequestHeader("Authorization", "Basic " + btoa(settings.kodi.username + ":" + settings.kodi.password));
                                    },
                                    success: function (response) {
                                        if ($.trim(response.result.item.title) !== "") {
                                            var bgImage = encodeURI(response.result.item.thumbnail);
                                            bgImage = settings.kodi.url.replace("//", "//" + settings.kodi.username + ":" + settings.kodi.password + "@") + "/image/" + bgImage;

                                            functions.createPlayer({
                                                type: "kodi",
                                                index: 99,
                                                bgImage: bgImage,
                                                title: response.result.item.artist,
                                                subtitle: response.result.item.title
                                            });
                                        }
                                    },
                                    complete: function () {
                                        settings.kodi.loading = false;
                                        functions.nowPlayingCallback();
                                    }
                                });
                            }
                        },
                        complete: function () {
                            settings.kodi.loading = false;
                            functions.nowPlayingCallback();
                        }
                    });
                }
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);
