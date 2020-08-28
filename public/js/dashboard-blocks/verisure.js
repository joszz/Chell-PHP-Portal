"use strict";

/**
* The gallery blocks on the dashboard.
*
* @class Gallery
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.verisure = function (options) {
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
                if (settings.block.length === 0) {
                    return;
                }

                settings.block.find(".fa-sync").click(functions.update);

                settings.updateIntervalId = window.setInterval(functions.update, settings.updateInterval);
            },

            /**
            * Calls the Verisure controller, Index action, to retrieve up to date information in JSON.
            * Sets the new values to the associated blocks and formats the data (stripping underscores from values and readable dates).
            *
            * @method update
            */
            update: function () {
                settings.block.isLoading();
                window.clearInterval(settings.updateIntervalId);

                $.ajax({
                    url: "verisure",
                    dataType: "json",
                    success: function (data) {
                        var date = new Date(data.armState.date);
                        var regexUnderscore = new RegExp("_", "g");
                        var status = data.armState.statusType.toLowerCase().capitalize().replace(regexUnderscore, " ");
                        var via = data.armState.changedVia.toLowerCase().capitalize().replace(regexUnderscore, " ");

                        settings.block.find(".status").html(status);
                        settings.block.find(".name").html(data.armState.name !== undefined ? data.armState.name : "Unknown");
                        settings.block.find(".date").html(date.getDate() + "-" + zeropad(date.getMonth() + 1, 2) + "-" + date.getFullYear() + " " + date.getHours() + ":" + date.getMinutes());
                        settings.block.find(".via").html(via);

                        var armstateIcon = settings.block.find("#amstate i");
                        switch (data.armState.statusType) {
                            default:
                                armstateIcon.attr("class", "fa fa-verisure-stay text-warning");
                                break;
                            case "DISARMED":
                                armstateIcon.attr("class", "fa fa-verisure-disarmed text-danger");
                                break;
                            case "ARMED":
                                armstateIcon.attr("class", "fa fa-verisure-away text-success");
                                break;
                        }

                        $.each(data.climateValues, function (_index, value) {
                            var tempBlock = settings.block.find("#" + value.deviceLabel.replace(/\s/g, '_'));
                            tempBlock.find(".value").html(value.temperature + "&deg;");
                            tempBlock.find("i").attr("class", "fa fa-thermometer-half " + value.cssClass);
                        });

                    },
                    complete: function (xhr) {
                        var timeout = settings.updateInterval;
                        if (xhr.status == 429) {
                            timeout *= 2;
                            console.log(xhr.errorMessage);
                        }

                        settings.updateIntervalId = window.setInterval(functions.update, timeout);
                        settings.block.isLoading("hide");
                    }
                });
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);