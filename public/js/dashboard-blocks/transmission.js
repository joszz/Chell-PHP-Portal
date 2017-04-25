"use strict";

/**
* The transmission block on the dashboard.
* 
* @class Transmission
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.transmission = function (options) {

        /**
        * All the settings for this block.
        * 
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            defaultData: {
                type: 'POST',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', 'Basic ' + btoa(settings.block.data('transmission-username') + ':' + settings.block.data('transmission-password')));
                    xhr.setRequestHeader('X-Transmission-Session-Id', settings.transmissionSessionId);
                },
                statusCode: {
                    409: function (request, status, error) {
                        settings.transmissionSessionId = request.getResponseHeader('X-Transmission-Session-Id');
                    }
                },
                url: this.data('transmission-url')
            },
            block: this,
            transmissionSessionId: -1,
            updateInterval: this.data('transmission-update-interval') * 1000,
            updateIntervalId: -1
        }, options);

        /**
        * All the functions for this block.
        * 
        * @property functions
        * @type Object
        */
        var functions = {

            /**
            * Retrieves the torrent list from Transmission.
            * 
            * @method getTorrents
            * @param {Boolean} onload Whether this function is called during onload or not. Optional, defaults to false.
            * @returns {Object}     Reference to self.
            */
            getTorrents: function (onload) {
                onload = typeof onload === 'undefined' ? false : onload;

                if (!onload) {
                    settings.block.isLoading();
                }

                clearInterval(settings.updateIntervalId);
                settings.updateIntervalId = setInterval(function () {
                    functions.getTorrents(false);
                }, settings.updateInterval);

                settings.block.find(".fa-refresh").off().on("click", function () {
                    functions.getTorrents(false);

                    $(this).blur();
                });

                var data = settings.defaultData;

                data.data = '{"method":"torrent-get", "arguments":{"fields":["id", "name", "percentDone", "status"]}}';
                data.complete = function (xhr, status) {
                    if (xhr.status === 200) {
                        var responseData = $.parseJSON(xhr.responseText);
                        settings.block.find('li').not('.hidden').remove();

                        if (responseData.arguments.torrents.length === 0) {
                            var torrent = settings.block.find('li.hidden').clone();
                            torrent.removeClass('hidden');
                            torrent.find('.torrentname').html('No torrents found');
                            torrent.find('.torrentname').removeClass("col-xs-4");
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
                            if (value.status === 4) {
                                torrent.find('.torrentactions .status').removeClass('fa-play');
                                torrent.find('.torrentactions .status').addClass('fa-pause');
                            }
                            //Paused
                            else if (value.status === 0) {
                                torrent.find('.torrentactions .status').removeClass('fa-pause');
                                torrent.find('.progress-bar').removeClass('progress-bar-success').addClass('progress-bar-primary');
                                torrent.find('.torrentactions .status').addClass('fa-play');
                            }

                            torrent.find('.torrentactions .status').off().on('click', function () {
                                self.startStopTorrents($(this).closest('li').data('id'), self);
                            });

                            torrent.find('.torrentactions .fa-remove').off().on('click', function () {
                                openConfirmDialog('Delete torrent?', [], function () {
                                    $.fancybox.close();

                                    if ($(this).attr('id') === 'confirm-yes') {
                                        functions.removeTorrents(torrent.data('id'));
                                    }
                                });
                            });

                            torrent.appendTo($('.transmission ul'));
                        });
                    }
                    //No sessionID set, do function again
                    else if (xhr.status === 409) {
                        if (!onload) {
                            settings.block.isLoading('hide');
                        }

                        functions.getTorrents(onload);
                        return;
                    }

                    if (!onload) {
                        settings.block.isLoading('hide');
                    }
                };

                $.ajax(data);

                return self;
            },

            /**
            * Given a torrentId, will toggle the state of the torrent to paused / started. Depending on the current state of the torrent.
            * 
            * @method startStopTorrents
            * @param {Number} torrentIds The torrent ID to stop/start.
            * @todo Rewrite so an array of torrents can be passed along.
            */
            startStopTorrents: function (torrentIds) {
                var data = settings.defaultData;
                data.data = '{"method":"torrent-' + ($('li[data-id=' + torrentIds + '] button.status:first-child').hasClass('fa-pause') ? 'stop' : 'start-now') + '", "arguments":{"ids":[' + torrentIds + ']}}';

                data.complete = function (xhr, status) {
                    //No sessionID set, do function again
                    if (xhr.status === 409) {
                        functions.startTorrents(torrentIds);
                    }
                    else if (xhr.status === 200) {
                        var responseData = $.parseJSON(xhr.responseText);
                        if (responseData.result === 'success') {
                            $('li[data-id=' + torrentIds + '] button.status').toggleClass('fa-pause fa-play');
                            $('li[data-id=' + torrentIds + '] .progress-bar').toggleClass('progress-bar-success progress-bar-primary');
                        }
                    }
                };

                $.ajax(data);
            },

            /**
            * Given a torrentId, will remove a torrent and local data.
            * 
            * @method removeTorrents
            * @param {Number} torrentIds The torrent ID to remove.
            * @todo Rewrite so an array of torrents can be passed along.
            */
            removeTorrents: function (torrentIds) {
                var data = settings.defaultData;
                data.data = '{"method":"torrent-remove", "arguments":{"ids":[' + torrentIds + ']}, "delete-local-data": true}';

                data.complete = function (xhr, status) {
                    //No sessionID set, do function again
                    if (xhr.status === 409) {
                        functions.removeTorrents(torrentIds);
                    }
                    else if (xhr.status === 200) {
                        var responseData = $.parseJSON(xhr.responseText);
                        if (responseData.result === 'success') {
                            $('li[data-id=' + torrentIds + ']').remove();
                            functions.getTorrents(false);
                        }
                    }
                };

                $.ajax(data);
            }
        };

        functions.getTorrents(true);

        return functions;
    };
})(jQuery);