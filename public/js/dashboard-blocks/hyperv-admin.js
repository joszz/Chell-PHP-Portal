"use strict";

/**
* The devices block on the dashboard.
* 
* @class Devices
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.hypervadmin = function (options) {

        /**
        * All the settings for this block.
        * 
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this
        }, options);

        /**
        * All the functions for this block.
        * 
        * @property functions
        * @type Object
        */
        var functions = {

            /**
            * Initializes the eventhandlers for the various button clicks.
            * 
            * @method checkstates
            */
            initialize: function () {
                settings.block.find(".togglestate:visible").click(function () {
                    openConfirmDialog("test", [], function () {
                        functions.toggleState();
                    });

                    return false;
                });
            },

            toggleState: function () {
                alert();
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);