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
                url: $(this).data("phpsysinfo-url"),
                vCore: $(this).data("phpsysinfo-vcore"),
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
                    settings.block.find(".fa-refresh").off().on("click", function () {
                        if (settings.block.hasClass("processes")) {
                            functions.psstatus();
                        }
                        else {
                            functions.getAll();
                        }
                    });
                },

                /**
                * Wrapper function to retrieve all data except psstatus plugin.
                * 
                * @method getAll
                * @todo incorporate the psstatus update in this as well, since we retrieve the data anyways.
                */
                getAll: function () {
                    $(".sysinfo, #hardware, .harddisks").isLoading();

                    var d = new Date();

                    $.ajax({
                        url: settings.url + "xml.php?json&" + d.getTime(),
                        dataType: "json",
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('Authorization', 'Basic ' + btoa(settings.block.data('phpsysinfo-username') + ':' + settings.block.data('phpsysinfo-password')));
                        },
                        success: function (data) {
                            functions.setSysinfo(data);
                            functions.setCPUCores(data);
                            functions.setNetwork(data);
                            functions.setRAM(data);
                            functions.setDisks(data);
                            functions.setUpdateNotifier(data);

                            $(".sysinfo, #hardware, .harddisks").isLoading("hide");
                        }
                    });
                },

                /**
                * Wrapper function to retrieve all data except psstatus plugin.
                * 
                * @method getAll
                * @param {Object} data The data retrieved from PHPSysInfo.
                * @todo incorporate the psstatus update in this as well, since we retrieve the data anyways.
                */
                setSysinfo: function (data) {
                    $("div.host").html(data.Vitals["@attributes"].Hostname);

                    $("div.distro span").html(data.Vitals["@attributes"].Distro);
                    $("div.distro div.icon").css("background-image", "url('" + settings.url + "gfx/images/" + data.Vitals["@attributes"].Distroicon + "')");

                    $("div.kernel").html(data.Vitals["@attributes"].Kernel);
                    $(".cpu-model-label").html(data.Hardware.CPU.CpuCore[0]["@attributes"].Model);
                    $("div.motherboard").html(data.Hardware["@attributes"].Name);

                    var date = new Date();
                    date.setSeconds(date.getSeconds() - Math.floor(data.Vitals["@attributes"].Uptime));

                    if ($("div.uptime").data("tinyTimer") !== undefined) {
                        clearInterval($("div.uptime").data("tinyTimer").interval);
                    }
                    $("div.uptime").tinyTimer({ from: date, format: "%d days %0h:%0m:%0s" });
                },

                /**
                * Finds .cpu-cores by index, than sets data for cpu cores, retrieved from PHPSysInfo.
                * 
                * @method setCPUCores
                * @param {Object} data The data retrieved from PHPSysInfo.
                */
                setCPUCores: function (data) {
                    $.each(data.Hardware.CPU.CpuCore, function (index, value) {
                        var coreSpeedCurrent = Math.round(value["@attributes"].CpuSpeed / 10) / 100 + " GHz";
                        var coreSpeedMin = Math.round(value["@attributes"].CpuSpeedMin / 10) / 100 + " GHz";
                        var coreSpeedMax = Math.round(value["@attributes"].CpuSpeedMax / 10) / 100 + " GHz";
                        var coreLabel, coreTemp;

                        $.each(data.MBInfo.Temperature.Item, function (indexTemps, valueTemps) {
                            if ($.trim(valueTemps["@attributes"].Label.toLowerCase()) === "core " + index) {
                                coreLabel = valueTemps["@attributes"].Label;
                                coreTemp = valueTemps["@attributes"].Value + " &deg;" + data.Options["@attributes"].tempFormat.toUpperCase();
                            }
                        });

                        var core = $("li.cpu-cores:eq(" + index + ")");
                        core.find(".cpu-core").html(coreLabel);
                        core.find(".core-temp").html(coreTemp);
                        core.find(".core-current").html(coreSpeedCurrent);
                        core.find(".core-min").html(coreSpeedMin);
                        core.find(".core-max").html(coreSpeedMax);
                    });
                },

                /**
                * Finds .lan-stats by index, than sets data for network, retrieved from PHPSysInfo.
                * 
                * @method setNetwork
                * @param {Object} data The data retrieved from PHPSysInfo.
                */
                setNetwork: function (data) {
                    $.each(data.Network.NetDevice, function (index, value) {
                        var rx = Math.round(value['@attributes'].RxBytes / 1024 / 1024 / 1024 * 100) / 100 + " GB";
                        var tx = Math.round(value['@attributes'].TxBytes / 1024 / 1024 / 1024 * 100) / 100 + " GB";
                        var info = value['@attributes'].Info.split(";");

                        network = $(".lan-stats div:eq(" + index + ")");

                        network.find(".lan-name").html(value.Name);
                        network.find(".lan-mac").html(info[0]);
                        network.find(".lan-ip").html(info[1]);
                        network.find(".lan-speed").html(info[2]);
                        network.find(".lan-rx").html(rx);
                        network.find(".lan-tx").html(tx);
                    });
                },

                /**
                * Finds .ra, and .swap, than sets data retrieved from PHPSysInfo. If no swap data found in PHPSysInfo data, hide .swap.
                * 
                * @method setRAM
                * @param {Object} data The data retrieved from PHPSysInfo.
                */
                setRAM: function (data) {
                    $("div.ram").find(".progress-bar").css("width", data.Memory["@attributes"].Percent + "%");
                    $("div.ram").find(".percent span").html(data.Memory["@attributes"].Percent);

                    //SWAP
                    if (data.Memory.Swap !== undefined) {
                        $("div.swap").find(".progress-bar").css("width", data.Memory.Swap["@attributes"].Percent + "%");
                        $("div.swap").find(".percent span").html(data.Memory.Swap["@attributes"].Percent);
                    }
                    else {
                        $("div.swap").closest("li").hide();
                    }
                },

                /**
                * Finds .harddisks li by index, than sets data retrieved from PHPSysInfo.
                * 
                * @method setDisks
                * @param {Object} data The data retrieved from PHPSysInfo.
                */
                setDisks: function (data) {
                    data.FileSystem.Mount.sort(function (a, b) {
                        return a["@attributes"].MountPoint < b["@attributes"].MountPoint ? -1 : 1;
                    });

                    $.each(data.FileSystem.Mount, function (index, value) {
                        var disk = $(".harddisks li:eq(" + index + ")");
                        var percent = value["@attributes"].Percent + "%";

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
                * @method setDisks
                * @param {Object} data The data retrieved from PHPSysInfo.
                */
                setUpdateNotifier: function (data) {
                    if (data.Plugins.Plugin_UpdateNotifier !== undefined) {
                        settings.block.find("span.packages").html("Packages:" + data.Plugins.Plugin_UpdateNotifier.UpdateNotifier.packages);
                        settings.block.find("span.security").html("Security:" + data.Plugins.Plugin_UpdateNotifier.UpdateNotifier.security);
                    }
                },

                /**
                * Retrieves psstatus data from PHPSysInfo using AJAX.<br />
                * Then clears all processess and dynamically create new nodes and appends them to the block.
                * 
                * @method psstatus
                */
                psstatus: function () {
                    $(".processes").isLoading();

                    settings.block.find(".fa-refresh").off().on("click", function () {
                        functions.psstatus();

                        $(this).blur();
                        return false;
                    });

                    var d = new Date();

                    $.ajax({
                        url: settings.url + "xml.php?plugin=psstatus&json&" + d.getTime(),
                        dataType: "json",
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('Authorization', 'Basic ' + btoa(settings.block.data('phpsysinfo-username') + ':' + settings.block.data('phpsysinfo-password')));
                        },
                        success: function (data) {
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

                            $(".processes").isLoading("hide");
                        }
                    });
                }
            };

            functions.initialize();

            return functions;
        });
    };
})(jQuery);