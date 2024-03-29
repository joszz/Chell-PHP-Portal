﻿"use strict";

/**
* The verisure widget.
*
* @class Verisure
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
            photos: []
        }, options);

        /**
        * All the functions for this block.
        *
        * @property functions
        * @type Object
        */
        var functions = {

            /**
            * Initializes the eventhandlers for various actions on the widget. Calls update to retrieve the content of the widget.
            *
            * @method initialize
            */
            initialize: function () {
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

                settings.block.find(".fa-rotate").click(function () { functions.update(false); });
                settings.block.find(".fa-image").click(functions.show_photos);
                settings.block.find(".fa-camera").click(functions.select_device);
                settings.block.find(".top button").click(function () {
                    $.fancybox.open({
                        src: "#verisure_set_armstate"
                    });
                });

                functions.update(true);
            },

            /**
             * Updates the widget by calling the verisure controller.
             * 
             * @param {boolean} initialize  Whether this function is called on initialize or not.
             */
            update: function (initialize) {
                initialize = typeof initialize === "undefined" ? false : initialize;
                if (!initialize) {
                    settings.block.isLoading();
                    clearInterval(settings.updateIntervalId);
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

                        $.each(data.climates, function (_index, value) {
                            var deviceId = value.device.deviceLabel.replace(/\s/g, '_');
                            var tempBlock = settings.block.find("#" + deviceId);

                            if (tempBlock.length === 0) {
                                tempBlock = $(".temps .clone").clone();
                                tempBlock.attr("id", deviceId);
                                tempBlock.removeClass("hidden clone");

                                tempBlock.appendTo($(".temps"));
                            }

                            tempBlock.find(".area span").text(value.device.area);
                            tempBlock.find(".value").html(value.temperatureValue + "&deg;");
                            tempBlock.find("i").attr("class", "fa fa-temperature-half " + value.cssClass);
                        });

                        var devices = $("#verisure_device_select");
                        devices.find("button:not(.hidden)").remove();
                        $.each(data.cameras, function (_index, value) {
                            var camera = devices.find("button.hidden").clone();
                            camera.data("device-label", value.device.deviceLabel);
                            camera.html(value.device.area);
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

                        settings.updateIntervalId = setInterval(functions.update, timeout);
                        settings.block.isLoading("hide");
                    }
                });

                functions.get_photos();
            },

            /**
             * Sets the alarm state to the given state.
             * 
             * @param {string} state   The state to set the alarm to.
             */
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

            /**
             * Shows the correct buttons depending on the given alarm state.
             * @param {string} state   The alarm state
             */
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
                        break;

                    case "DISARMED":
                        $("#verisure_set_armstate #arm_stay").removeClass("hidden");
                        $("#verisure_set_armstate #arm_away").removeClass("hidden");
                        $("#verisure_set_armstate #disarm").addClass("hidden");

                        $("#verisure_set_armstate #current_stay").addClass("hidden");
                        $("#verisure_set_armstate #current_away").addClass("hidden");
                        $("#verisure_set_armstate #current_disarmed").removeClass("hidden");

                        widgetCurrentButton.attr("class", "fa fa-verisure-disarmed text-danger");
                        break;

                    case "ARMED_AWAY":
                        $("#verisure_set_armstate #arm_stay").addClass("hidden");
                        $("#verisure_set_armstate #arm_away").addClass("hidden");
                        $("#verisure_set_armstate #disarm").removeClass("hidden");

                        $("#verisure_set_armstate #current_stay").addClass("hidden");
                        $("#verisure_set_armstate #current_away").removeClass("hidden");
                        $("#verisure_set_armstate #current_disarmed").addClass("hidden");

                        widgetCurrentButton.attr("class", "fa fa-verisure-away text-success");
                        break;
                }

                var regexUnderscore = new RegExp("_", "g");
                widgetCurrentButton.attr("title", state.toLowerCase().capitalize().replace(regexUnderscore, " "));
            },

            /**
             * Gets all the photos and sets them to settings.photos. At the end of the AJAX call, callBackSuccess is called, if supplied.
             * 
             * @param {function} callbackSuccess    The function to call when the photos have been retrieved.
             */
            get_photos: function (callbackSuccess) {
                $.ajax({
                    url: "verisure/imageseries/",
                    dataType: "json",
                    success: function (data) {
                        if (!data) {
                            return;
                        }

                        settings.photos = [];

                        if (data.mediaSeriesList.length) {
                            settings.block.find(".fa-image").removeClass("hidden");
                        }

                        $.each(data.mediaSeriesList, function (_index, seriesList) {
                            $.each(seriesList.deviceMediaList, function (_index, mediaList) {
                                var date = new Date(mediaList.timestamp);
                                var humanReadableDate = date.getDate() + "-" + zeropad(date.getMonth() + 1, 2) + "-" + date.getFullYear() + " " + zeropad(date.getHours(), 2) + ":" + zeropad(date.getMinutes(), 2);

                                settings.photos.push({
                                    src: "verisure/image/" + encodeURI(mediaList.deviceLabel) + "/" + encodeURI(mediaList.mediaId) + "/" + encodeURI(mediaList.timestamp),
                                    //caption: serie.area + " (" + humanReadableDate + ")",
                                    type: "image",
                                    opts: {
                                        caption: humanReadableDate,
                                        deviceLabel: mediaList.deviceLabel
                                    }
                                });
                            });
                        });

                        settings.photos.sort(function (a, b) {
                            return a.caption > b.caption ? -1 : 1;
                        });

                        if (settings.photos.length) {
                            settings.block.find(".fa-image").removeClass("hidden");
                        }
                        else {
                            settings.block.find(".fa-image").addClass("hidden");
                        }

                        if (callbackSuccess) {
                            callbackSuccess();
                        }
                    }
                });
            },

            /**
             * Retrieves the current photos. Opens a fancybox modal and shows the retrieved photos.
             */
            show_photos: function () {
                functions.get_photos(function () {
                    $.fancybox.open(settings.photos, {
                        buttons: [
                            "take_photo",
                            "close"
                        ],
                        slideClass: "verisure",
                        loop: true
                    });
                });
            },

            /**
             * Sents a request to take a photo for the given device label.
             * 
             * @param {string} device_label     The camera's device label
             */
            take_photo: function (device_label) {
                var currentAmountOfPhotos = settings.photos.length;

                $.ajax({
                    url: "verisure/captureimage/" + device_label,
                    dataType: "json",
                    success: function (_data) {
                        $.fancybox.getInstance().close();

                        var intervalId = setInterval(function () {
                            functions.get_photos(function () {
                                if (settings.photos.length > currentAmountOfPhotos) {
                                    clearInterval(intervalId);
                                    showAlert("success", "photo taken");
                                }
                            });
                        }, 5000);
                    },
                    error: function () {
                        showAlert("danger", "Something went wrong");
                    }
                });
            },

            /**
             * Shows a Fancybox modal to select a device/camera.
             * 
             * @method select_device
             */
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