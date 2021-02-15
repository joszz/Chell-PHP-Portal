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
            updateIntervalId: -1,
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

                $.fancybox.defaults.btnTpl.take_photo = "<button data-fancybox-take-photo class='fancybox-button fa fa-camera' title='Take photo'></button>";

                $("body").on("click", "[data-fancybox-take-photo]", function () {
                    functions.take_photo(encodeURI($.fancybox.getInstance().current.deviceLabel));
                });

                $("body").on("click", "#verisure_device_select button", function () {
                    functions.take_photo(encodeURI($(this).data("deviceLabel")));
                });

                $("body").on("click", "#verisure_set_armstate button", function () {
                    functions.set_arm_state($(this).data("state"));
                });

                settings.block.find(".fa-sync").click(function () { functions.update(false); });
                settings.block.find(".fa-camera").click(function () {
                    functions.photos(true)
                });
                settings.block.find(".fa-image").click(functions.select_device);
                settings.block.find(".top button").click(function () {
                    $.fancybox.open({
                        src: "#verisure_set_armstate"
                    });
                });

                functions.photos();
                functions.update(true);
            },

            /**
            * Calls the Verisure controller, Index action, to retrieve up to date information in JSON.
            * Sets the new values to the associated blocks and formats the data (stripping underscores from values and readable dates).
            *
            * @method update
            */
            update: function (initialize) {
                initialize = typeof initialize === "undefined" ? false : initialize;
                if (!initialize) {
                    settings.block.isLoading();
                    window.clearInterval(settings.updateIntervalId);
                }

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
                        settings.block.find(".date").html(date.getDate() + "-" + zeropad(date.getMonth() + 1, 2) + "-" + date.getFullYear() + " " + zeropad(date.getHours(), 2) + ":" + zeropad(date.getMinutes(), 2));
                        settings.block.find(".via").html(via);

                        functions.set_arm_state_buttons(data.armState.statusType);

                        $.each(data.climateValues, function (_index, value) {
                            var deviceId = value.deviceLabel.replace(/\s/g, '_');
                            var tempBlock = settings.block.find("#" + deviceId);

                            if (tempBlock.length === 0) {
                                tempBlock = $(".temps .clone").clone();
                                tempBlock.attr("id", deviceId);
                                tempBlock.removeClass("hidden clone");

                                tempBlock.appendTo($(".temps"));
                            }

                            tempBlock.find(".area span").text(value.deviceArea);
                            tempBlock.find(".value").html(value.temperature + "&deg;");
                            tempBlock.find("i").attr("class", "fa fa-thermometer-half " + value.cssClass);
                        });

                        var devices = $("#verisure_device_select");
                        devices.find("button:not(.hidden)").remove();
                        $.each(data.customerImageCameras, function (_index, value) {
                            var camera = devices.find("button.hidden").clone();
                            camera.data("device-label", value.deviceLabel);
                            camera.html(value.area);
                            camera.removeClass("hidden");
                            camera.appendTo(devices);
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
            },

            set_arm_state: function (state) {
                var pin = $("#verisure_set_armstate #verisure_pin").val();

                $.ajax({
                    url: "verisure/arm/" + state + "/" + (pin !== undefined ? pin : ""),
                    success: function (_data) {
                        showAlert("success", "Alarm state changed");
                        $.fancybox.getInstance().close();
                        functions.set_arm_state_buttons(state);
                    },
                    error: function () {
                        showAlert("danger", "Something went wrong");
                    }
                });
            },

            set_arm_state_buttons: function (state) {
                var widgetCurrentButton = settings.block.find("#armstate button");

                switch (state) {
                    default:
                        $("#verisure_set_armstate #arm_stay").addClass("hidden");
                        $("#verisure_set_armstate #arm_away").addClass("hidden");
                        $("#verisure_set_armstate #disarm").removeClass("hidden");

                        $("#verisure_set_armstate #current_stay").removeClass("hidden");
                        $("#verisure_set_armstate #current_away").addClass("hidden");
                        $("#verisure_set_armstate #current_disarmed").addClass("hidden");

                        widgetCurrentButton.attr("class", "fa fa-verisure-stay text-warning");
                        settings.block.find(".fa-image").addClass("hidden");
                        break;

                    case "DISARMED":
                        $("#verisure_set_armstate #arm_stay").removeClass("hidden");
                        $("#verisure_set_armstate #arm_away").removeClass("hidden");
                        $("#verisure_set_armstate #disarm").addClass("hidden");

                        $("#verisure_set_armstate #current_stay").addClass("hidden");
                        $("#verisure_set_armstate #current_away").addClass("hidden");
                        $("#verisure_set_armstate #current_disarmed").removeClass("hidden");

                        widgetCurrentButton.attr("class", "fa fa-verisure-disarmed text-danger");
                        settings.block.find(".fa-image").addClass("hidden");
                        break;

                    case "ARMED_AWAY":
                        $("#verisure_set_armstate #arm_stay").addClass("hidden");
                        $("#verisure_set_armstate #arm_away").addClass("hidden");
                        $("#verisure_set_armstate #disarm").removeClass("hidden");

                        $("#verisure_set_armstate #current_stay").addClass("hidden");
                        $("#verisure_set_armstate #current_away").removeClass("hidden");
                        $("#verisure_set_armstate #current_disarmed").addClass("hidden");

                        widgetCurrentButton.attr("class", "fa fa-verisure-away text-success");
                        settings.block.find(".fa-image").removeClass("hidden");
                        break;
                }
            },

            photos: function (openFancyBox) {
                openFancyBox = typeof openFancyBox === "undefined" ? false : openFancyBox;

                $.ajax({
                    url: "verisure/imageseries/",
                    dataType: "json",
                    success: function (data) {
                        var photos = [];

                        if (data.imageSeries.length) {
                            settings.block.find(".fa-camera").removeClass("hidden");
                        }

                        $.each(data.imageSeries, function (_index, serie) {
                            var date = new Date(serie.image[0].captureTime);
                            var humanReadableDate = date.getDate() + "-" + zeropad(date.getMonth() + 1, 2) + "-" + date.getFullYear() + " " + zeropad(date.getHours(), 2) + ":" + zeropad(date.getMinutes(), 2);

                            photos.push({
                                src: "verisure/image/" + encodeURI(serie.deviceLabel) + "/" + encodeURI(serie.image[0].imageId) + "/" + encodeURI(serie.image[0].captureTime),
                                caption: serie.area + " (" + humanReadableDate + ")",
                                deviceLabel: serie.deviceLabel
                            });
                        });

                        photos.sort(function (a, b) {
                            return a.caption > b.caption ? -1 : 1;
                        });

                        if (openFancyBox) {
                            $.fancybox.open(photos, {
                                buttons: [
                                    settings.block.find("#amstate button").hasClass("fa-verisure-away") ? "take_photo" : null,
                                    "close"
                                ],
                                loop: true
                            });
                        }
                    }
                });
            },

            take_photo: function (device_label) {
                $.ajax({
                    url: "verisure/captureimage/" + device_label,
                    dataType: "json",
                    success: function (_data) {
                        $.fancybox.getInstance().close();

                        window.setTimeout(function () {
                            $.ajax({
                                url: "verisure/imageseries/",
                                dataType: "json",
                                success: function (data) {
                                    settings.block.find(".fa-camera").removeClass("hidden");
                                    showAlert("success", "photo taken");
                                }
                            });
                        }, 30000);
                    },
                    error: function () {
                        showAlert("danger", "Something went wrong");
                    }
                });
            },

            select_device: function () {
                $.fancybox.open({
                    src: "#verisure_device_select"
                });
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);