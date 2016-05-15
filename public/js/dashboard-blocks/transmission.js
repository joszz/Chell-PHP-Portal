(function ($) {
    $.fn.transmission = function (options) {
        var settings = $.extend({
            transmissionDefaultData: {
                type: "POST",
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Authorization", "Basic " + btoa(settings.transmissionBlock.data("transmission-username") + ":" + settings.transmissionBlock.data("transmission-password")));
                    xhr.setRequestHeader("X-Transmission-Session-Id", settings.transmissionSessionId);
                },
                statusCode: {
                    409: function (request, status, error) {
                        settings.transmissionSessionId = request.getResponseHeader("X-Transmission-Session-Id")
                    }
                },
                url: this.data("transmission-url")
            },
            transmissionBlock: this,
            transmissionSessionId: -1,
            updateInterval: this.data("transmission-update-interval"),
            updateIntervalId: -1
        }, options);

        var functions =  {
            getTorrents: function (onload, self) {
                self = typeof self === 'undefined' ? this : self;
                onload = typeof onload === 'undefined' ? false : onload;

                if (!onload) {
                    settings.transmissionBlock.isLoading({
                        text: "Loading",
                        position: "overlay"
                    });
                }

                clearInterval(settings.updateIntervalId);
                settings.updateIntervalId = setInterval(function (functions) {
                    self.getTorrents(false, self);
                }, settings.updateInterval * 1000);

                var data = settings.transmissionDefaultData;

                data.data = '{"method":"torrent-get", "arguments":{"fields":["id", "name", "percentDone", "status"]}}';
                data.complete = function (xhr, status) {
                    //No sessionID set, do function again
                    if (xhr.status == 409) {
                        self.getTorrents(onload, self);
                    }
                    else if (xhr.status == 200) {
                        var responseData = $.parseJSON(xhr.responseText);

                        $.each(responseData.arguments.torrents, function (index, value) {
                            var torrent;

                            //todo: do not clone but create inline if not exists, so on update new torrents will be added
                            if (onload) {
                                torrent = settings.transmissionBlock.find("li:first").clone();
                                torrent.attr("data-id", value.id);
                                torrent.removeClass("hidden");
                                torrent.find(".torrentname").html(value.name);
                            }
                            else {
                                torrent = settings.transmissionBlock.find("li[data-id=" + value.id + "]");
                            }

                            torrent.find(".torrentprogress .percent").html((value.percentDone * 100) + "%");
                            torrent.find(".torrentprogress .progress-bar").width(value.percentDone * 100);

                            if (value.status == 4) {
                                torrent.find(".torrentactions .status").removeClass("glyphicon-play");
                                torrent.find(".torrentactions .status").addClass("glyphicon-pause");
                            }
                            else if (value.status == 0) {
                                torrent.find(".torrentactions .status").removeClass("glyphicon-pause");
                                torrent.find(".torrentactions .status").addClass("glyphicon-play");
                            }

                            torrent.find(".torrentactions .status").attr("href", torrent.find(".torrentactions .status").attr("href") + value.id);
                            torrent.find(".torrentactions .status").off().on("click", function () {
                                self.startTorrents($(this).closest("li").data("id"), self);
                            });

                            if (onload) {
                                torrent.appendTo($(".transmission ul"));
                            }
                        });

                        if (!onload) {
                            settings.transmissionBlock.isLoading("hide");
                        }
                    }
                };

                $.ajax(data);

                return self;
            },

            startTorrents: function (torrentId, self) {
                self = typeof self === 'undefined' ? this : self;

                var data = settings.transmissionDefaultData;
                data.data = '{"method":"torrent-start-now", "arguments":{"ids":[' + torrentId + ']}}';
                data.complete = function (xhr, status) {
                    //No sessionID set, do function again
                    if (xhr.status == 409) {
                        self.transmissionStartTorrents(torrentId, self);
                    }
                    else if (xhr.status == 200) {
                        var responseData = $.parseJSON(xhr.responseText);
                        if (responseData.result == "success") {
                            $("li[data-id=" + torrentId + "] button.status").toggleClass("glyphicon-pause glyphicon-play");
                        }
                    }
                };

                $.ajax(data);
            }
        };

        return functions;
    };
})(jQuery);