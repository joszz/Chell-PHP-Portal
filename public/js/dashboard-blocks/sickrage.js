"use strict";

/**
* @todo comments
* @class Sickrage
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.sickrage = function (options) {

        /**
        * All the settings for this block.
        * 
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            apiKey: this.data("sickrage-apikey"),
            baseUrl: this.data("sickrage-url"),
        }, options);

        /**
        * All the functions for this block.
        * 
        * @property functions
        * @type Object
        */
        var functions = {

            /**
            * Initializes the eventhandlers for button clicks to navigate between gallery items and sets the auto rotate interval for the gallery.
            * 
            * @method initialize
            */
            initialize: function () {
                settings.block.find(".fa-refresh").on("click", function () {
                    functions.refresh();
                });

                functions.refresh(true);
            },

            refresh: function (onload) {
                if (!onload) {
                    settings.block.isLoading();
                }

                $.ajax({
                    url: functions.url("future", "today|soon"),
                    dataType: "json",
                    success: function (data) {
                        data = data.data;

                        var ulToday = settings.block.find("#today .list-group");
                        var ulSoon = settings.block.find("#soon .list-group");

                        ulToday.find("a:not(.hidden)").remove();
                        ulSoon.find("a:not(.hidden)").remove();

                        $.each(data.today, function (index, value) {
                            functions.createItems(ulToday, value);
                        });

                        $.each(data.soon, function (index, value) {
                            functions.createItems(ulSoon, value);
                        });
                    },

                    complete: function () {
                        if (!onload) {
                            settings.block.isLoading("hide");
                        }
                    }
                });
            },

            createItems: function (ul, value) {
                var item = ul.find("a.hidden").clone();
                var episode = item.find(".episode");
                var detail = item.find(".sickrage-detail");
                var episodeText = "S" + zeropad(value.season, 2) + "E" + zeropad(value.episode, 2);
                var id = "t" + value.tvdbid + "-" + episodeText;

                episode.html(episodeText + " | " + value.show_name);

                detail.attr("id", id);
                detail.find("h4").html(value.show_name)

                var detailEpisode = $("<div class='col-xs-12'></div>");
                detailEpisode.html(episodeText);
                detailEpisode.appendTo(detail.find(".panel-body"));

                item.on("click", function () {
                    $.fancybox.open({
                        src: "#" + id,
                        margin: [0]
                    });

                    return false;
                })

                item.removeClass("hidden");
                item.prependTo(ul);
            },

            url: function (cmd, type) {
                return settings.baseUrl + "api/" + settings.apiKey + "/?cmd=" + cmd + "&type=" + type;
            }
        };

        functions.initialize();
    };
})(jQuery);