(function ($) {
    $.fn.transmission = function (options) {
        var settings = $.extend({
            defaultData: {
                type: 'POST',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', 'Basic ' + btoa(settings.transmissionBlock.data('transmission-username') + ':' + settings.transmissionBlock.data('transmission-password')));
                    xhr.setRequestHeader('X-Transmission-Session-Id', settings.transmissionSessionId);
                },
                statusCode: {
                    409: function (request, status, error) {
                        settings.transmissionSessionId = request.getResponseHeader('X-Transmission-Session-Id')
                    }
                },
                url: this.data('transmission-url')
            },
            transmissionBlock: this,
            transmissionSessionId: -1,
            updateInterval: this.data('transmission-update-interval'),
            updateIntervalId: -1
        }, options);

        var functions =  {
            getTorrents: function (onload, self) {
                self = typeof self === 'undefined' ? this : self;
                onload = typeof onload === 'undefined' ? false : onload;

                if (!onload) {
                    settings.transmissionBlock.isLoading({
                        text: 'Loading',
                        position: 'overlay'
                    });
                }

                clearInterval(settings.updateIntervalId);
                settings.updateIntervalId = setInterval(function (functions) {
                    self.getTorrents(false, self);
                }, settings.updateInterval * 1000);

                var data = settings.defaultData;

                data.data = '{"method":"torrent-get", "arguments":{"fields":["id", "name", "percentDone", "status"]}}';
                data.complete = function (xhr, status) {
                    //No sessionID set, do function again
                    if (xhr.status == 409) {
                        self.getTorrents(onload, self);
                    }
                    else if (xhr.status == 200) {
                        var responseData = $.parseJSON(xhr.responseText);
                        settings.transmissionBlock.find('li').not('.hidden').remove();

                        $.each(responseData.arguments.torrents, function (index, value) {
                            var torrent = settings.transmissionBlock.find('li.hidden').clone();
                            torrent.attr('data-id', value.id);
                            torrent.removeClass('hidden');
                            torrent.find('.torrentname').html(value.name);

                            value.percentDone = Math.round(value.percentDone * 10000) / 100;
                            torrent.find('.torrentprogress .percent').html(value.percentDone + '%');
                            torrent.find('.torrentprogress .progress-bar').width(value.percentDone + '%');

                            //Downloading
                            if (value.status == 4) {
                                torrent.find('.torrentactions .status').removeClass('glyphicon-play');
                                torrent.find('.torrentactions .status').addClass('glyphicon-pause');
                            }
                            //Paused
                            else if (value.status == 0) {
                                torrent.find('.torrentactions .status').removeClass('glyphicon-pause');
                                torrent.find('.progress-bar').removeClass('progress-bar-success').addClass('progress-bar-primary');
                                torrent.find('.torrentactions .status').addClass('glyphicon-play');
                            }

                            torrent.find('.torrentactions .status').off().on('click', function () {
                                self.startStopTorrents($(this).closest('li').data('id'), self);
                                return false;
                            });

                            torrent.find('.torrentactions .glyphicon-remove').off().on('click', function () {
                                self.removeTorrents($(this).closest('li').data('id'), self);
                                return false;
                            });

                             torrent.appendTo($('.transmission ul'));
                        });

                        settings.transmissionBlock.isLoading('hide');
                    }
                };

                $.ajax(data);

                return self;
            },

            startStopTorrents: function (torrentIds, self) {
                self = typeof self === 'undefined' ? this : self;

                var data = settings.defaultData;
                data.data = '{"method":"torrent-' + ($('li[data-id=' + torrentIds + '] button.status:first-child').hasClass('glyphicon-pause') ? 'stop' : 'start-now') + '", "arguments":{"ids":[' + torrentIds + ']}}';

                data.complete = function (xhr, status) {
                    //No sessionID set, do function again
                    if (xhr.status == 409) {
                        self.startTorrents(torrentIds, self);
                    }
                    else if (xhr.status == 200) {
                        var responseData = $.parseJSON(xhr.responseText);
                        if (responseData.result == 'success') {
                            $('li[data-id=' + torrentIds + '] button.status').toggleClass('glyphicon-pause glyphicon-play');
                            $('li[data-id=' + torrentIds + '] .progress-bar').toggleClass('progress-bar-success progress-bar-primary');
                        }
                    }
                };

                $.ajax(data);
            },

            removeTorrents: function (torrentIds, self) {
                self = typeof self === 'undefined' ? this : self;

                var data = settings.defaultData;
                data.data = '{"method":"torrent-remove", "arguments":{"ids":[' + torrentIds + ']}, "delete-local-data: true}';

                data.complete = function (xhr, status) {
                    //No sessionID set, do function again
                    if (xhr.status == 409) {
                        self.removeTorrents(torrentIds, self);
                    }
                    else if (xhr.status == 200) {
                        var responseData = $.parseJSON(xhr.responseText);
                        if (responseData.result == 'success') {
                            $('li[data-id=' + torrentIds + ']').remove();
                        }
                    }
                };

                $.ajax(data);
            },
        };

        return functions;
    };
})(jQuery);