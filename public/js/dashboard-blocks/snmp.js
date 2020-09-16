"use strict";

/**
* The gallery blocks on the dashboard.
*
* @class Gallery
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.snmp = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: $(this),
            updateInterval: $(this).data("update-interval") * 1000,
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
            * Initializes the eventhandlers for;
            * - button clicks to navigate between SNMP hosts.
            * - Detail page click, changing the URL to currently visible SNMP host Id.
            * and sets up the interval to auto update the content.
            *
            * @method initialize
            */
            initialize: function () {
                settings.block.find(".fa-chevron-left, .fa-chevron-right").click(function () {
                    var offset = $(this).hasClass("fa-chevron-right") ? 1 : -1;
                    var currentHost = settings.block.find(".host:visible");
                    var currentIndex = currentHost.index();

                    var nextHost = settings.block.find(".host:eq(" + (currentIndex + offset) + ")");
                    var nextIndex = nextHost.length === 1 ? currentIndex + offset : 0;
                    nextHost = settings.block.find(".host:eq(" + nextIndex + ")");

                    if (currentIndex !== nextIndex) {
                        currentHost.fadeOut("fast", function () {
                            nextHost.fadeIn("fast").css("display", "block");
                        });
                    }
                });

                //change details button in header
                settings.block.find("a").click(function () {
                    $(this).attr("href", "/portal/snmp/details/" + settings.block.find(".host:visible").data("id"));
                });
                settings.block.find(".fa-sync").click(function () {
                    functions.update();
                });

                settings.updateIntervalId = window.setInterval(functions.update, settings.updateInterval);

                settings.block.find(".host").each(function (_index, host) {
                    $.get("snmp/hostcontent/" + $(host).data("id") + "/hidden-xs", function (html) {
                        $(host).html(html);
                        initializeTinyTimer($(host).find(".time"));
                    });
                });
            },

            /**
            * Updates the currently visible SNMP host. First destroys the TinyTimer to reinitiate after content is updated.
            *
            * @method update
            */
            update: function (hostToUpdate) {
                settings.block.isLoading();

                if (hostToUpdate === undefined) {
                    hostToUpdate = settings.block.find(".host:visible");
                }
                window.clearInterval(settings.updateIntervalId);

                var time = hostToUpdate.find("div.time");

                if (time.data("tinyTimer") !== undefined) {
                    clearInterval(time.data("tinyTimer").interval);
                }

                $.get("snmp/hostcontent/" + hostToUpdate.data("id"), function (html) {
                    hostToUpdate.html(html);
                    initializeTinyTimer(hostToUpdate.find(".time"));
                });

                settings.updateIntervalId = window.setInterval(functions.update, settings.updateInterval);
                settings.block.isLoading("hide");
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);