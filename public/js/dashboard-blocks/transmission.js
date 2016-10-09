(function ($) {
    $.fn.transmission = function (options) {
        var settings = $.extend({
            defaultData: {
                type: 'POST',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', 'Basic ' + btoa(settings.block.data('transmission-username') + ':' + settings.block.data('transmission-password')));
                    xhr.setRequestHeader('X-Transmission-Session-Id', settings.transmissionSessionId);
                },
                statusCode: {
                    409: function (request, status, error) {
                        settings.transmissionSessionId = request.getResponseHeader('X-Transmission-Session-Id')
                    }
                },
                url: this.data('transmission-url')
            },
            block: this,
            transmissionSessionId: -1,
            updateInterval: this.data('transmission-update-interval') * 1000,
            updateIntervalId: -1
        }, options);

        var functions = {
            getTorrents: function (onload) {
                onload = typeof onload === 'undefined' ? false : onload;

                if (!onload) {
                    settings.block.isLoading();
                }

                clearInterval(settings.updateIntervalId);
                settings.updateIntervalId = setInterval(function () {
                    functions.getTorrents(false);
                }, settings.updateInterval);

                settings.block.find(".glyphicon-refresh").off().on("click", function () {
                    functions.getTorrents(false);

                    $(this).blur();
                });

                var data = settings.defaultData;

                data.data = '{"method":"torrent-get", "arguments":{"fields":["id", "name", "percentDone", "status"]}}';
                data.complete = function (xhr, status) {
                    //No sessionID set, do function again
                    if (xhr.status == 409) {
                        if (!onload) {
                            settings.block.isLoading('hide');
                        }

                        functions.getTorrents(onload);
                    }
                    else if (xhr.status == 200) {
                        var responseData = $.parseJSON(xhr.responseText);
                        settings.block.find('li').not('.hidden').remove();

                        if (responseData.arguments.torrents.length == 0) {
                            var torrent = settings.block.find('li.hidden').clone();
                            torrent.removeClass('hidden');
                            torrent.find('.torrentname').html('No torrents found');
                            torrent.find('.torrentprogress, .torrentactions').remove();
                            torrent.appendTo($('.transmission ul'));
                        }

                        $.each(responseData.arguments.torrents, function (index, value) {
                            var torrent = settings.block.find('li.hidden').clone();
                            torrent.attr('data-id', value.id);
                            torrent.removeClass('hidden');
                            torrent.find('.torrentname').html(value.name);
                            torrent.find('.torrentname').attr("title", value.name);

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
                            });

                            torrent.find('.torrentactions .glyphicon-remove').off().on('click', function () {
                                openConfirmDialog('Delete torrent?', [], function () {
                                    $.fancybox.close();

                                    if ($(this).attr('id') == 'confirm-yes') {
                                        functions.removeTorrents(torrent.data('id'));
                                    }
                                });
                            });

                            torrent.appendTo($('.transmission ul'));
                        });

                        if (!onload) {
                            settings.block.isLoading('hide');
                        }
                    }
                };

                $.ajax(data);

                return self;
            },

            startStopTorrents: function (torrentIds) {
                var data = settings.defaultData;
                data.data = '{"method":"torrent-' + ($('li[data-id=' + torrentIds + '] button.status:first-child').hasClass('glyphicon-pause') ? 'stop' : 'start-now') + '", "arguments":{"ids":[' + torrentIds + ']}}';

                data.complete = function (xhr, status) {
                    //No sessionID set, do function again
                    if (xhr.status == 409) {
                        functions.startTorrents(torrentIds);
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

            removeTorrents: function (torrentIds) {
                var data = settings.defaultData;
                data.data = '{"method":"torrent-remove", "arguments":{"ids":[' + torrentIds + ']}, "delete-local-data": true}';

                data.complete = function (xhr, status) {
                    //No sessionID set, do function again
                    if (xhr.status == 409) {
                        functions.removeTorrents(torrentIds);
                    }
                    else if (xhr.status == 200) {
                        var responseData = $.parseJSON(xhr.responseText);
                        if (responseData.result == 'success') {
                            $('li[data-id=' + torrentIds + ']').remove();
                            functions.getTorrents(false);
                        }
                    }
                };

                $.ajax(data);
            },
        };

        functions.getTorrents(true);

        return functions;
    };
})(jQuery);