"use strict";

/**
* The gallery blocks on the dashboard.
* 
* @class Gallery
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
                $.ajax({
                    url: "https://josnienhuis.gotgeeks.com/sickrage/api/13288ed59fa17830f389741758909318/?cmd=future&type=today|soon",
                    dataType: "json",
                    success: function (data) {

                    }
                });
            }
        };

        functions.initialize();
    };
})(jQuery);