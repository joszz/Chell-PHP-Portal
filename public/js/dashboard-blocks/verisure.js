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

                settings.block.find(".fa-sync").click(functions.update);
                settings.block.find(".fa-camera").click(functions.photos);
                settings.block.find(".fa-image").click(functions.select_device);
                settings.block.find(".top button").click(function () {
                    $.fancybox.open({
                        src: "#verisure_set_armstate"
                    });
                });

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
                        settings.block.find(".date").html(date.getDate() + "-" + zeropad(date.getMonth() + 1, 2) + "-" + date.getFullYear() + " " + zeropad(date.getHours(), 2) + ":" + zeropad(date.getMinutes(), 2));
                        settings.block.find(".via").html(via);

                        var armstateButton = settings.block.find("#amstate button");
                        switch (data.armState.statusType) {
                            default:
                                armstateButton.attr("class", "fa fa-verisure-stay text-warning");
                                settings.block.find(".fa-image").addClass("hidden");
                                break;

                            case "DISARMED":
                                armstateButton.attr("class", "fa fa-verisure-disarmed text-danger");
                                settings.block.find(".fa-image").addClass("hidden");
                                break;

                            case "ARMED_AWAY":
                                armstateButton.attr("class", "fa fa-verisure-away text-success");
                                settings.block.find(".fa-image").removeClass("hidden");
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
            },

            set_arm_state: function (state) {

                $.ajax({
                    url: "verisure/arm/" + state + "/" + $("#verisure_set_armstate #verisure_pin").val(),
                    success: function (_data) {
                        showAlert("success", "Alarm state changed");
                        $.fancybox.getInstance().close();
                        functions.update();

                        switch (state) {
                            default:
                                settings.block.find("#verisure_set_armstate .fa-verisure-stay").addClass("hidden");
                                settings.block.find("#verisure_set_armstate .fa-verisure-away").removeClass("hidden");
                                settings.block.find("#verisure_set_armstate .fa-verisure-disarmed").removeClass("hidden");
                                break;

                            case "DISARMED":
                                settings.block.find("#verisure_set_armstate .fa-verisure-disarmed").addClass("hidden");
                                settings.block.find("#verisure_set_armstate .fa-verisure-stay").removeClass("hidden");
                                settings.block.find("#verisure_set_armstate .fa-verisure-away").removeClass("hidden");
                                break;

                            case "ARMED_AWAY":
                                settings.block.find("#verisure_set_armstate .fa-verisure-away").addClass("hidden");
                                settings.block.find("#verisure_set_armstate .fa-verisure-stay").removeClass("hidden");
                                settings.block.find("#verisure_set_armstate .fa-verisure-disarmed").removeClass("hidden");
                                break;
                        }
                    },
                    error: function () {
                        showAlert("danger", "Something went wrong");
                    }
                });
            },

            photos: function () {
                var photos = [];
                var imageSeries = $.parseJSON(settings.block.find(".fa-camera").attr("data-photos")).imageSeries;

                $.each(imageSeries, function (_index, serie) {
                    var date = new Date(serie.image[0].captureTime);
                    var humanReadableDate = date.getDate() + "-" + zeropad(date.getMonth() + 1, 2) + "-" + date.getFullYear() + " " + zeropad(date.getHours(), 2) + ":" + zeropad(date.getMinutes(), 2);

                    photos.push({
                        src: "/portal/verisure/image/" + encodeURI(serie.deviceLabel) + "/" + encodeURI(serie.image[0].imageId) + "/" + encodeURI(serie.image[0].captureTime),
                        caption: serie.area + " (" + humanReadableDate +  ")",
                        deviceLabel: serie.deviceLabel
                    });
                });

                photos.sort(function (a, b) {
                    return a.caption > b.caption ? -1 : 1;
                });

                $.fancybox.open(photos, {
                    buttons: [
                        settings.block.find("#amstate button").hasClass("fa-verisure-away") ? "take_photo" : null,
                        "close"
                    ],
                    loop: true
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
                                    settings.block.find(".fa-camera").attr("data-photos", JSON.stringify(data)).removeClass("hidden");
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