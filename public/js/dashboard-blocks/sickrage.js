"use strict";

/**
* The Sickrage block on the dashboard.
* 
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
            baseUrl: this.data("sickrage-url")
        }, options);

        /**
        * All the functions for this block.
        * 
        * @property functions
        * @type Object
        */
        var functions = {

            /**
            * Initializes the eventhandler for refreshing the content and calls refresh the retrieve content immediately.
            * 
            * @method initialize
            */
            initialize: function () {
                settings.block.find(".fa-refresh").on("click", function () {
                    functions.refresh();
                });

                functions.refresh(true);
            },

            /**
             * Refreshes the contents of the Sickrage block.
             * 
             * @method refresh
             * @param {Boolean} onload  whether this call is made onload of the webpage or not.
             */
            refresh: function (onload) {
                if (!onload) {
                    settings.block.isLoading();
                }

                var today = settings.block.find("#today .list-group");
                var soon = settings.block.find("#soon .list-group");

                $.ajax({
                    url: functions.url("future", "today|soon"),
                    dataType: "json",
                    success: function (data) {
                        data = data.data;

                        today.find("a:not(.hidden)").remove();
                        soon.find("a:not(.hidden)").remove();

                        $.each(data.today, function (index, value) {
                            functions.createItems(today, value);
                        });

                        $.each(data.soon, function (index, value) {
                            functions.createItems(soon, value);
                        });
                    },

                    complete: function () {
                        if (!onload) {
                            settings.block.isLoading("hide");
                        }

                        if (!today.find(".list-group-item:not(.hidden)").length) {
                            settings.block.find("a[href='#soon']").tab("show");
                            settings.block.find("a[href='#today']").parent().addClass("disabled");
                            settings.block.find("a[href='#today']").attr("data-toggle", "");
                        }
                        else {
                            settings.block.find("a:eq(0)").attr("data-toggle", "tab");
                        }
                    }
                });
            },

            /**
             * Called by functions.refresh to create items for the content retrieved from Sickrage through AJAX.
             * 
             * @method createItems
             * @param {Object} ul       The list to add the item to.   
             * @param {Object} value    The value retrieved by AJAX used to fill the newly created listitem.
             */
            createItems: function (ul, value) {
                var item = ul.find("a.hidden").clone();
                var episode = item.find(".episode");
                var detail = item.find(".sickrage-detail");
                var episodeText = "S" + zeropad(value.season, 2) + "E" + zeropad(value.episode, 2);
                var id = "t" + value.tvdbid + "-" + episodeText;

                episode.html(episodeText + " | " + value.show_name);

                detail.attr("id", id);
                detail.find("h4").html(value.show_name);

                var detailEpisode = $("<div class='col-xs-12'></div>");
                detailEpisode.html(episodeText);
                detailEpisode.appendTo(detail.find(".panel-body"));

                item.on("click", function () {
                    $.fancybox.open({
                        src: "#" + id,
                        margin: [0]
                    });

                    return false;
                });

                item.removeClass("hidden");
                item.prependTo(ul);
            },

            /**
             * Returns the Sickrage API URL given a cmd and type.
             * 
             * @method url
             * @param {String}      cmd      The command to sent to the Sickrage API.
             * @param {String}      type     The type to sent to the Sickrage API.
             * @returns {String}    The Sickrage API URL string to use for the AJAX calls.
             */
            url: function (cmd, type) {
                return settings.baseUrl + "api/" + settings.apiKey + "/?cmd=" + cmd + "&type=" + type;
            }
        };

        functions.initialize();
    };
})(jQuery);