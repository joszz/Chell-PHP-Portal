"use strict";

/**
* The devices block on the dashboard.
*
* @class Devices
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.devices = function (options) {

        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            updateInterval: this.data("device-state-interval") * 1000,
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
            * Initializes the eventhandlers for the various button clicks.
            *
            * @method checkstates
            */
            initialize: function () {
                if (settings.block.length === 0) {
                    return;
                }

                settings.block.on("click", ".fa-sync", function () {
                    clearInterval(settings.updateIntervalId);
                    settings.updateIntervalId = setInterval(function () {
                        functions.checkstates();
                    }, settings.updateInterval);

                    functions.checkstates();
                });

                settings.block.on("click", ".btn-danger", function () {
                    var title = "Wake <span>" + $(this).closest("li").find("div:first").html().trim() + "</span>?";
                    openConfirmDialog(title, [{ id: $(this).data("id") }], function () {
                        functions.wake($(this));
                    });
                });

                settings.block.on("click", ".btn-success", function () {
                    var title = "Shutdown <span>" + $(this).closest("li").find("div:first").html().trim() + "</span>?";

                    openConfirmDialog(title, [{ id: $(this).data("id") }], function () {
                        functions.shutdown($(this));
                    });
                });

                functions.checkstates();
            },

            /**
            * Checks the states of each device. Loops through the devices and makes an AJAX call to retrieve their on/off state.
            *
            * @method checkstates
            */
            checkstates: function () {
                var d = new Date();

                clearInterval(settings.updateIntervalId);
                settings.updateIntervalId = setInterval(function () {
                    functions.checkstates();
                }, settings.updateInterval);

                settings.block.find(".devicestate").each(function () {
                    var device = $(this);
                    var icon = $(this).find("span.fa");
                    var id = $(this).data("id");
                    var dependentMenuItems = $("ul.nav li[data-id='" + id + "'");

                    dependentMenuItems.find("a").bind("click", false);

                    $(this).removeClass("btn-danger btn-success");
                    $(this).addClass("disabled");

                    icon.removeClass("fa-power-off");
                    icon.addClass("fa-sync fa-spin");

                    (function (device, dependentMenuItems, icon, id) {
                        $.getJSON("devices/state/" + id + "/" + d.getTime(), "", function (data) {
                            icon.removeClass("fa-sync fa-spin");
                            icon.addClass("fa-power-off");

                            device.removeClass("disabled");

                            if (device.data("shutdown-method") === "none") {
                                device.addClass("disabled");
                            }

                            if (!data["state"]) {
                                dependentMenuItems.addClass("disabled");
                                device.parent().find(".hypervadmin").addClass("disabled");
                            }
                            else {
                                dependentMenuItems.removeClass("disabled");
                                device.parent().find(".hypervadmin").removeClass("disabled");
                                dependentMenuItems.find("a").unbind("click", false);
                            }

                            device.addClass("btn-" + (data["state"] ? "success" : "danger"));
                        });
                    }(device, dependentMenuItems, icon, id));
                });
            },

            /**
             * Shows a confirmation dialog. If confirmed with yes, sents a wakeup call.
             * 
             * @method wake
             */
            wake: function (btn) {
                $.fancybox.close();

                if (btn.attr("id") === "confirm-yes") {
                    var name = btn.closest("div").find("h2 span").html().trim();

                    $.get("devices/wake/" + btn.closest("div").data("id"), function (data) {
                        var alertType = "danger";
                        var alertMessage = "Wakeup command failed for: " + name;

                        if (data) {
                            alertType = "success";
                            alertMessage = "Wakeup command send to: " + name;
                        }

                        showAlert(alertType, alertMessage);
                        functions.checkstates();
                    });
                }
            },

            /**
             * Shows a confirmation dialog. If confirmed with yes, sents a shutdown call.
             *
             * @method shutdown
             */
            shutdown: function (btn) {
                $.fancybox.close();

                if (btn.attr("id") === "confirm-yes") {
                    var parentDiv = btn.closest("div");
                    var id = parentDiv.data("id");
                    var name = parentDiv.closest("div").find("h2 span").html().trim();

                    $.get("devices/shutdown/" + id, function (data) {
                        var alertType = "danger";
                        var alertMessage = "Shutdown command failed for: " + name;

                        if (data) {
                            alertType = "success";
                            alertMessage = "Shutdown command send to: " + name;
                        }

                        showAlert(alertType, alertMessage);
                        functions.checkstates();
                    });
                }
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);