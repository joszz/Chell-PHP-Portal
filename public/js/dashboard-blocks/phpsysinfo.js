"use strict";

/**
* The various blocks on the dashboard that build upon PHPSysInfo data.
*
* @class PHPSysInfo
* @module Dashboard
* @submodule DashboardBlocks
* @example http://phpsysinfo.github.io/phpsysinfo/
*/
(function ($) {
    $.fn.phpsysinfo = function (options) {
        this.each(function () {
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

                    settings.block.find(".fa-sync").off().on("click", function () {
                        if (settings.block.hasClass("processes")) {
                            functions.getPsStatus();
                        }
                        else {
                            functions.getAll();
                        }
                    });

                    if (settings.block.hasClass("sysinfo")) {
                        //use timeout to prevent isloading from positioning incorrectly on load
                        functions.getAll(true);
                    }
                },

                /**
                * Wrapper function to retrieve all data except psstatus plugin.
                *
                * @method getAll
                * @todo incorporate the psstatus update in this as well, since we retrieve the data anyways.
                */
                getAll: function (initialize) {
                    initialize = typeof initialize === "undefined" ? false : initialize;
                    if (!initialize) {
                        $(".sysinfo, #hardware:visible, .harddisks, .processes").isLoading();
                    }

                    $.ajax({
                        url: "phpsysinfo/",
                        dataType: "json",
                        success: function (data) {
                            var hardwareBlock = $("#hardware");
                            var sysinfoBlock = $(".sysinfo");
                            var diskBlock = $(".harddisks");

                            functions.setSysinfo(data, sysinfoBlock);
                            functions.setCPUCores(data, hardwareBlock);
                            functions.setNetwork(data, hardwareBlock);
                            functions.setRAM(data, sysinfoBlock);
                            functions.setDisks(data, diskBlock);
                            functions.setUpdateNotifier(data, sysinfoBlock);
                            functions.setPsStatus(data);
                        },
                        complete: function () {
                            $(".sysinfo, #hardware:visible, .harddisks, .processes").isLoading("hide");
                        }
                    });
                },

                /**
                * Wrapper function to retrieve all data except psstatus plugin.
                *
                * @method getAll
                * @param {object} data The data retrieved from PHPSysInfo.
                * @todo incorporate the psstatus update in this as well, since we retrieve the data anyways.
                */
                setSysinfo: function (data, block) {
                    block.find("div.host").html(data.Vitals["@attributes"].Hostname);
                    block.find("div.distro span").html(data.Vitals["@attributes"].Distro);
                    block.find("div.distro div.icon").css("background-image", "url('" + settings.url + "gfx/images/" + data.Vitals["@attributes"].Distroicon + "')");
                    block.find("div.kernel").html(data.Vitals["@attributes"].Kernel);

                    var date = new Date();
                    date.setSeconds(date.getSeconds() - Math.floor(data.Vitals["@attributes"].Uptime));

                    if (settings.block.find("div.time").data("tinyTimer") !== undefined) {
                        clearInterval(settings.block.find("div.time").data("tinyTimer").interval);
                    }
                    block.find("div.time").tinyTimer({ from: date, format: "%d days %0h:%0m:%0s" });
                },

                /**
                * Finds .cpu-cores by index, than sets data for cpu cores, retrieved from PHPSysInfo.
                *
                * @method setCPUCores
                * @param {object} data The data retrieved from PHPSysInfo.
                */
                setCPUCores: function (data, block) {
                    block.find(".cpu-model .value").html(data.Hardware.CPU.CpuCore[0]["@attributes"].Model);
                    block.find(".motherboard .value").html(data.Hardware["@attributes"].Name);

                    $.each(data.Hardware.CPU.CpuCore, function (index, value) {
                        var coreSpeedCurrent = Math.round(value["@attributes"].CpuSpeed / 10) / 100 + " GHz";
                        var coreSpeedMin = Math.round(value["@attributes"].CpuSpeedMin / 10) / 100 + " GHz";
                        var coreSpeedMax = Math.round(value["@attributes"].CpuSpeedMax / 10) / 100 + " GHz";
                        var coreLabel = "Core " + index;
                        var coreTemp;

                        $.each(data.MBInfo.Temperature.Item, function (_indexTemps, valueTemps) {
                            if ($.trim(valueTemps["@attributes"].Label.toLowerCase()).indexOf("core " + index) !== -1) {
                                coreTemp = valueTemps["@attributes"].Value + " &deg;" + data.Options["@attributes"].tempFormat.toUpperCase();
                            }
                        });

                        var core = block.find("li.cpu-cores:not(.clone)").eq(index);

                        if (core.length === 0) {
                            core = block.find("li.cpu-cores.clone").clone();
                            core.removeClass("hidden clone");
                            core.insertBefore(block.find(".network"));
                        }

                        if (!coreTemp) {
                            core.find(".core-temp, .core-temp-label").remove();
                        }
                        else {
                            core.find(".core-temp").html(coreTemp);
                        }

                        core.find(".cpu-core").html(coreLabel);
                        core.find(".core-current").html(coreSpeedCurrent);
                        core.find(".core-min").html(coreSpeedMin);
                        core.find(".core-max").html(coreSpeedMax);
                    });
                },

                /**
                * Finds .lan-stats by index, than sets data for network, retrieved from PHPSysInfo.
                *
                * @method setNetwork
                * @param {object} data The data retrieved from PHPSysInfo.
                */
                setNetwork: function (data, block) {
                    if (!$.isArray(data.Network.NetDevice)) {
                        data.Network.NetDevice = new Array(data.Network.NetDevice);
                    }

                    $.each(data.Network.NetDevice, function (index, value) {
                        if (typeof value["@attributes"] !== "undefined") {
                            var rx = Math.round(value["@attributes"].RxBytes / 1024 / 1024 / 1024 * 100) / 100 + " GB";
                            var tx = Math.round(value["@attributes"].TxBytes / 1024 / 1024 / 1024 * 100) / 100 + " GB";
                            var info = value["@attributes"].Info.split(";");
                            var network = block.find(".lan-stats > div:not(.clone)").eq(index);

                            if (network.length === 0) {
                                network = block.find(".lan-stats > .clone").clone();
                                network.removeClass("hidden clone");
                                network.appendTo(block.find(".lan-stats"));
                            }

                            network.find(".lan-name").html(value["@attributes"].Name);
                            network.find(".lan-mac").html(info[0]);
                            network.find(".lan-ip").html(info[1]);
                            network.find(".lan-speed").html(info[info.length - 1]);
                            network.find(".lan-rx").html(rx);
                            network.find(".lan-tx").html(tx);
                        }
                    });
                },

                /**
                * Finds .ra, and .swap, than sets data retrieved from PHPSysInfo. If no swap data found in PHPSysInfo data, hide .swap.
                *
                * @method setRAM
                * @param {object} data The data retrieved from PHPSysInfo.
                */
                setRAM: function (data, block) {
                    block.find("div.ram").find(".progress-bar").css("width", data.Memory["@attributes"].Percent + "%");
                    block.find("div.ram").find(".percent span").html(data.Memory["@attributes"].Percent);

                    //SWAP
                    if (data.Memory.Swap !== undefined) {
                        block.find("div.swap").find(".progress-bar").css("width", data.Memory.Swap["@attributes"].Percent + "%");
                        block.find("div.swap").find(".percent span").html(data.Memory.Swap["@attributes"].Percent);
                    }
                    else {
                        block.find("div.swap").closest("li").hide();
                    }
                },

                /**
                * Finds .harddisks li by index, than sets data retrieved from PHPSysInfo.
                *
                * @method setDisks
                * @param {object} data The data retrieved from PHPSysInfo.
                */
                setDisks: function (data, block) {
                    data.FileSystem.Mount.sort(function (a, b) {
                        return a["@attributes"].MountPoint < b["@attributes"].MountPoint ? -1 : 1;
                    });

                    $.each(data.FileSystem.Mount, function (index, value) {
                        var disk = block.find("li:not(.clone)").eq(index);

                        if (disk.length === 0) {
                            disk = block.find("li.clone").clone();
                            disk.removeClass("hidden clone");
                            disk.appendTo(block.find("ul"));
                        }

                        var percent = parseInt(value["@attributes"].Percent) + (value["@attributes"].Buffers !== undefined ? parseInt(value["@attributes"].Buffers) : 0) + "%";

                        disk.find(".name").html(value["@attributes"].MountPoint);
                        disk.find(".progress-bar").css("width", percent);
                        disk.find(".percent").html(percent);

                        if (value["@attributes"].Percent > 90) {
                            disk.find(".progress-bar").addClass("progress-bar-danger");
                        }
                        else if (value["@attributes"].Percent > 70) {
                            disk.find(".progress-bar").addClass("progress-bar-warning");
                        }
                        else if (value["@attributes"].Percent > 50) {
                            disk.find(".progress-bar").addClass("progress-bar-info");
                        }
                        else {
                            disk.find(".progress-bar").addClass("progress-bar-success");
                        }
                    });
                },

                /**
                * Finds span.packages and span.security, than sets data retrieved from PHPSysInfo if this data is set.
                *
                * @method setUpdateNotifier
                * @param {object} data The data retrieved from PHPSysInfo.
                */
                setUpdateNotifier: function (data, block) {
                    if (data.Plugins.Plugin_UpdateNotifier !== undefined) {
                        block.find(".update .packages .value").html(data.Plugins.Plugin_UpdateNotifier.UpdateNotifier.packages);
                        block.find(".update .security .value").html(data.Plugins.Plugin_UpdateNotifier.UpdateNotifier.security);
                    }
                },

                /**
                * Retrieves psstatus data from PHPSysInfo using AJAX.
                * Then clears all processess and dynamically create new nodes and appends them to the block.
                *
                * @method getPsStatus
                */
                getPsStatus: function () {
                    $(".processes").isLoading();

                    $.ajax({
                        url: "phpsysinfo/index/psstatus",
                        dataType: "json",
                        success: function (data) {
                            functions.setPsStatus(data);
                        },
                        complete: function () {
                            $(".processes").isLoading("hide");
                        }
                    });
                },

                /**
                 * Sets the different statusses of processes retrieved from PHPSysInfo API.
                 * 
                 * @method setPsStatus
                 * @param {object} data The data retrieved from PHPSysInfo.
                 */
                setPsStatus: function (data) {
                    data.Plugins.Plugin_PSStatus.Process.sort(function (a, b) {
                        return a["@attributes"].Name < b["@attributes"].Name ? -1 : 1;
                    });

                    $.each(data.Plugins.Plugin_PSStatus.Process, function (index, value) {
                        var listItem = $("<li />", { class: "list-group-item col-xs-12 col-md-12" });
                        var name = $("<div />", { class: "col-xs-10" });
                        var status = $("<div />", { class: "col-xs-2 text-right" });
                        var label = $("<span />", { class: "label" });

                        name.html(value["@attributes"].Name);
                        name.appendTo(listItem);

                        if (value["@attributes"].Status === "1") {
                            label.html("On");
                            label.addClass("label-success");
                        }
                        else {
                            label.html("Off");
                            label.addClass("label-danger");
                        }

                        label.appendTo(status);
                        status.appendTo(listItem);

                        $("div.processes li div:contains('" + value["@attributes"].Name + "')").parent().remove();
                        listItem.appendTo($("div.processes ul"));
                    });
                }
            };

            functions.initialize();

            return functions;
        });
    };
})(jQuery);