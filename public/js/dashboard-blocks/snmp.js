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
        this.each(function () {

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
                * Initializes the eventhandlers for button clicks to navigate between SNMP hosts.
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
                    settings.block.find(".fa-sync").click(functions.update);

                    settings.updateIntervalId = window.setInterval(functions.update, settings.updateInterval);
                },

                update: function () {
                    settings.block.isLoading();
                    window.clearInterval(settings.updateIntervalId);

                    var currentHost = settings.block.find(".host:visible");
                    $.get("snmp/hostcontent/" + currentHost.data("id"), function (html) {
                        currentHost.html(html);
                        initializeTinyTimer(currentHost.find('.time'));
                    });

                    settings.updateIntervalId = window.setInterval(functions.update, settings.updateInterval);
                    settings.block.isLoading('hide');
                }
            };

            functions.initialize();
        });
    };
})(jQuery);