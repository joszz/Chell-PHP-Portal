"use strict";

/**
* The transmission block on the dashboard.
* 
* @class Transmission
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.torrents = function (options) {

        /**
        * All the settings for this block.
        * 
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            transmissionSessionId: -1,
            updateInterval: this.data('update-interval') * 1000,
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

                settings.block.find(".fa-sync").off().on("click", function () {
                    functions.getTorrents(false);
                });

                $.getJSON({
                    url: "torrents/",
                    success: function (data) {
                        settings.block.find('li').not('.hidden').remove();

                        if (data.length === 0) {
                            var torrent = settings.block.find('li.hidden').clone();
                            torrent.removeClass('hidden');
                            torrent.find('.torrentname').html('No torrents found');
                            torrent.find('.torrentname').removeClass("col-xs-4");
                            torrent.find('.torrentprogress, .torrentactions').remove();
                            torrent.appendTo($('.torrents ul'));
                        }

                        $.each(data, function (_index, value) {
                            var torrent = settings.block.find('li.hidden').clone();
                            torrent.attr('data-id', value.id);
                            torrent.removeClass('hidden');
                            torrent.find('.torrentname').html(value.name);
                            torrent.find('.torrentname').attr("title", value.name);

                            value.percentDone = Math.round(value.percentDone * 10000) / 100;
                            torrent.find('.torrentprogress .percent').html(value.percentDone + '%');
                            torrent.find('.torrentprogress .progress-bar').width(value.percentDone + '%');

                            //Downloading
                            if (value.status === "downloading") {
                                torrent.find('.torrentactions .status').removeClass('fa-play');
                                torrent.find('.torrentactions .status').addClass('fa-pause');
                            }
                            //Paused
                            else if (value.status === "paused") {
                                torrent.find('.torrentactions .status').removeClass('fa-pause');
                                torrent.find('.progress-bar').removeClass('progress-bar-success').addClass('progress-bar-primary');
                                torrent.find('.torrentactions .status').addClass('fa-play');
                            }

                            torrent.find('.torrentactions .status').off().on('click', function () {
                                functions.startStopTorrents($(this).closest('li').data('id'));
                            });

                            torrent.find('.torrentactions .fa-trash-alt').off().on('click', function () {
                                openConfirmDialog('Delete torrent?', [], function () {
                                    $.fancybox.close();

                                    if ($(this).attr('id') === 'confirm-yes') {
                                        functions.removeTorrents(torrent.data('id'));
                                    }
                                });
                            });

                            torrent.appendTo($('.torrents ul'));
                        });
                    },
                    error: function () {
                        showAlert("danger", "Something went wrong");
                    }
                });

                if (!onload) {
                    settings.block.isLoading('hide');
                }

                return self;
            },

            /**
            * Given a torrentId, will toggle the state of the torrent to paused / started. Depending on the current state of the torrent.
            * 
            * @method startStopTorrents
            * @param {Number} torrentIds The torrent ID to stop/start.
            * @todo Rewrite so an array of torrents can be passed along.
            */
            startStopTorrents: function (torrentId) {
                var isPaused = !$('li[data-id=' + torrentId + '] button.status:first-child').hasClass('fa-pause');

                $.get({
                    url: "torrents/" + (isPaused ? "resume" : "pause") + "/" + torrentId,
                    success: function () {
                        $('li[data-id=' + torrentId + '] button.status').toggleClass('fa-pause fa-play');
                        $('li[data-id=' + torrentId + '] .progress-bar').toggleClass('progress-bar-success progress-bar-primary');
                    }
                });
            },

            /**
            * Given a torrentId, will remove a torrent and local data.
            * 
            * @method removeTorrents
            * @param {Number} torrentIds The torrent ID to remove.
            * @todo Rewrite so an array of torrents can be passed along.
            */
            removeTorrents: function (torrentId) {
                $.get({
                    url: "torrents/remove/" + torrentId,
                    success: function () {
                        $('li[data-id=' + torrentId + ']').remove();
                        functions.getTorrents(false);
                    }
                });
            }
        };

        if (settings.block.length !== 0) {
            functions.getTorrents(true);
        }

        return functions;
    };
})(jQuery);