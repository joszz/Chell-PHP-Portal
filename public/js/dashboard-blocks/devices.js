﻿/**
* The devices block on the dashboard.
* 
* @class Devices
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.devices = function (options) {

        /**
        * All the settings for this block
        * 
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            updateInterval: this.data('device-state-interval') * 1000,
            updateIntervalId: -1
        }, options);

        /**
        * All the functions for this block
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
                settings.block.on("click", ".glyphicon-refresh", function () {
                    clearInterval(settings.updateIntervalId);
                    settings.updateIntervalId = setInterval(function () {
                        self.checkstates(self);
                    }, settings.updateInterval);

                    functions.checkstates(settings.block);
                });

                settings.block.on("click", ".btn-danger", function () {
                    var title = "Wake <span>" + $(this).closest("li").find("div:first").html().trim() + "</span>?";
                    openConfirmDialog(title, [{ mac: $(this).data("mac") }], function () {
                        functions.wol($(this));
                    });
                });

                settings.block.on("click", ".btn-success", function () {
                    var title = "Shutdown <span>" + $(this).closest("li").find("div:first").html().trim() + "</span>?";

                    openConfirmDialog(title, [{
                        "shutdown-user": $(this).data("shutdown-user"),
                        "shutdown-password": $(this).data("shutdown-password"),
                        "ip": $(this).data("ip"),
                    }], function () {
                        functions.doShutdown($(this));
                    });
                });

                functions.checkstates();
            },

            /**
            * Checks the states of each device. Loops through the devices and makes an AJAX call to retrieve their on/off state.
            * 
            * @method checkstates
            */
            checkstates: function (self) {
                self = typeof self === 'undefined' ? this : self;
                var d = new Date();

                clearInterval(settings.updateIntervalId);
                settings.updateIntervalId = setInterval(function () {
                    self.checkstates(self);
                }, settings.updateInterval);

                settings.block.find(".devicestate").each(function () {
                    var device = $(this);
                    var icon = $(this).find("span.glyphicon");
                    var ip = $(this).data("ip");
                    var dependentMenuItems = $("ul.nav li[data-ip='" + ip + "'");

                    dependentMenuItems.find("a").bind("click", false);

                    $(this).removeClass("btn-danger btn-success");
                    $(this).addClass("disabled");

                    icon.removeClass("glyphicon-off");
                    icon.addClass("glyphicon-refresh icon-refresh-animate");

                    (function (device, dependentMenuItems, icon, ip) {
                        $.getJSON("devices/state?ip=" + ip + "&" + d.getTime(), "", function (data) {
                            icon.removeClass("glyphicon-refresh icon-refresh-animate");
                            icon.addClass("glyphicon-off");

                            device.removeClass("disabled");

                            if (device.data("shutdown-method") == "none") {
                                device.addClass("disabled");
                            }

                            if (!data["state"]) {
                                dependentMenuItems.addClass("disabled");
                            }
                            else {
                                dependentMenuItems.removeClass("disabled");
                                dependentMenuItems.find("a").unbind("click", false);
                            }

                            device.addClass("btn-" + (data["state"] ? "success" : "danger"));
                        });
                    }(device, dependentMenuItems, icon, ip));
                });
            },

            /**
            * Closes the modal dialog and if confim-yes was clicked, make an AJAX call to WakeupOnLan the selected device.
            * 
            * @method checkstates
            * @param btn {Object} Which button of the confirm dialog is pressed (yes/no).
            */
            wol: function (btn) {
                $.fancybox.close();

                if (btn.attr("id") == "confirm-yes") {
                    var name = btn.closest("div").find("h2 span").html().trim();

                    $.get("devices/wol?mac=" + btn.closest("div").data("mac"), function (name) {
                        $("div.alert").addClass("alert-success");
                        $("div.alert").html("Magic packet send to: " + name);
                        $("div.alert").fadeIn("fast");

                        fadeOutAlert();
                    }(name));
                }
            },

            /**
            * Closes the modal dialog and if confim-yes was clicked, make an AJAX call to shutdown the selected device.
            * 
            * @method checkstates
            * @param btn {Object} Which button of the confirm dialog is pressed (yes/no).
            */
            doShutdown: function (btn) {
                $.fancybox.close();

                if (btn.attr("id") == "confirm-yes") {
                    var parentDiv = btn.closest("div");
                    var user = parentDiv.data("shutdown-user");
                    var password = parentDiv.data("shutdown-password");
                    var ip = parentDiv.data("ip");
                    var name = parentDiv.closest("div").find("h2 span").html().trim();

                    $.get("devices/shutdown?ip=" + ip + "&user=" + user + " &password=" + password, function (data) {
                        if (data == "true") {
                            $("div.alert").addClass("alert-success");
                            $("div.alert").html("Shutdown command send to: " + name);
                        }
                        else {
                            $("div.alert").addClass("alert-danger");
                            $("div.alert").html("Shutdown command failed for: " + name);
                        }

                        $("div.alert").fadeIn("fast");
                        fadeOutAlert();
                    });
                }
            }
        }

        functions.initialize();

        return functions;
    }
})(jQuery);