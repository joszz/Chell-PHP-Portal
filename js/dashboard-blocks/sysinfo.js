"use strict";

/**
* The various blocks on the dashboard that build upon PHPSysInfo data.
*
* @class SysInfo
* @module Dashboard
* @submodule DashboardBlocks
* @example http://phpsysinfo.github.io/phpsysinfo/
*/
(function ($) {
    $.fn.sysinfo = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            url: $(this).data("distro-icon-base"),
            block: $(this)
        }, options);

        /**
        * All the functions for this block.
        *
        * @property functions
        * @type Object
        */
        var functions = {

            /**
            * Initializes the eventhandler for the refresh button. Because of limitations of PHPSysinfo.
            *
            * @method initialize
            */
            initialize: function () {
                if (settings.block.length === 0) {
                    return;
                }

                settings.block.find(".fa-rotate").off().on("click", functions.update);

                if (settings.block.hasClass("sysinfo")) {
                    functions.update();
                }
            },

            update: function () {
                $.ajax({
                    url: "sysinfo/",
                    dataType: "json",
                    success: function (data) {
                        settings.block.find("div.host").html(data.hostname);
                        settings.block.find("div.kernel").html(data.linux_kernel_version);
                        settings.block.find("div.ram").find(".progress-bar").css("width", data.memoryinfo.percentused + "%");
                        settings.block.find("div.ram").find(".percent span").html(data.memoryinfo.percentused);

                        var date = new Date();
                        date.setSeconds(date.getSeconds() - Math.floor(data.uptime.uptime));

                        if (settings.block.find("div.time").data("tinyTimer") !== undefined) {
                            clearInterval(settings.block.find("div.time").data("tinyTimer").interval);
                        }
                        settings.block.find("div.time").tinyTimer({ from: date, format: "%d days %0h:%0m:%0s" });
                    },
                    complete: function () {
                        $(".sysinfo").isLoading("hide");
                    }
                });
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);