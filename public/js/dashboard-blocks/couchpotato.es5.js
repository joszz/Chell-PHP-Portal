"use strict";

/**
* The Couchpotato block on the dashboard.
* 
* @class Couchpotato
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.couchpotato = function (options) {

        /**
        * All the settings for this block.
        * 
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            apiKey: this.data("couchpotato-apikey"),
            baseUrl: this.data("couchpotato-url")
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
            initialize: function initialize() {
                settings.block.find(".fa-refresh").on("click", function () {
                    functions.refresh(false);
                });

                settings.block.find(".fa-search").on("click", functions.searchWantedMovies);

                functions.refresh(true);
            },

            /**
             * Refreshes the contents of the Couchpotato block.
             * 
             * @method refresh
             * @param {Boolean} onload  whether this call is made onload of the webpage or not.
             */
            refresh: function refresh(onload) {
                if (!onload) {
                    settings.block.isLoading();
                    settings.block.find(".item").remove();
                }

                $.ajax({
                    url: functions.url("media.list"),
                    dataType: "json",
                    success: function success(data) {
                        $.each(data.movies, function (index, value) {
                            var clone = settings.block.find("a.clone").clone();

                            clone.removeClass("clone").addClass("item");
                            clone.attr({
                                "href": "/portal/couchpotato/movie/" + value._id,
                                "data-fancybox": "couchpotato",
                                "data-fancybox-type": "iframe",
                                "title": value.info.tagline
                            });

                            if (index === data.movies.length - 1) {
                                clone.removeClass("hidden_not_important");
                            }

                            clone.find(".title").html(value.title);
                            clone.find(".subtitle").html(value.info.tagline);

                            clone.css("background-image", "url('" + value.info.images.poster + "')");
                            clone.attr("title", value.title);

                            clone.prependTo(settings.block.find(".panel-body"));
                        });
                    },
                    complete: function complete() {
                        if (!onload) {
                            settings.block.isLoading("hide");
                        }
                    }
                });
            },

            /**
             * Calls the Couchpotato API to search the wanted movies.
             * 
             * @todo: comments
             * @todo: API call movie.searcher.progress? to show the progress?
             * @method searchWantedMovies
             */
            searchWantedMovies: function searchWantedMovies() {
                showAlert("success", "Initiating Couchpotato movie search");

                $.ajax({
                    url: functions.url("movie.searcher.full_search"),
                    dataType: "json"
                });
            },

            /**
             * Returns the Couchpotato API URL given a cmd.
             * 
             * @method url
             * @param {String}      cmd      The command to sent to the Couchpotato API.
             * @returns {String}    The Couchpotato API URL string to use for the AJAX calls.
             */
            url: function url(cmd) {
                return settings.baseUrl + "api/" + settings.apiKey + "/" + cmd + "/";
            }
        };

        functions.initialize();
    };
})(jQuery);

