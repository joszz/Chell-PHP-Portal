"use strict";

/**
* @todo comments
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
                    url: functions.url("media.list"),
                    dataType: "json",
                    success: function (data) {
                        $.each(data.movies, function (index, value) {
                            var clone = settings.block.find("a.clone").clone();
                            clone.removeClass("clone").addClass("item");

                            if (index === 0) {
                                clone.removeClass("hidden_not_important");
                            }

                            clone.find(".title").html(value.title);
                            clone.find(".subtitle").html(value.info.tagline);

                            clone.css("background-image", "url('" + value.info.images.poster + "')");
                            clone.attr("title", value.title);
                            
                            clone.prependTo(settings.block.find(".panel-body"));

                        });
                    },

                    complete: function () {
                        if (!onload) {
                            settings.block.isLoading("hide");
                        }
                    }
                });
            },

            url: function (cmd) {
                return settings.baseUrl + "api/" + settings.apiKey + "/" + cmd + "/";
            }
        };

        functions.initialize();
    };
})(jQuery);