(function ($) {
    $.fn.phpsysinfo = function (options) {
        var settings = $.extend({
            url: this.data("phpsysinfo-url"),
            vCore: this.data("phpsysinfo-vcore"),
            block: this
        }, options);
        
        return {
            getAll: function (onload, self) {
                self = typeof self === 'undefined' ? this : self;
                onload = typeof onload === 'undefined' ? false : onload;

                if (!onload) {
                    $(".sysinfo, .hardware, .harddisks").isLoading({
                        text: "Loading",
                        position: "overlay"
                    });
                }

                settings.block.find(".glyphicon-refresh").off().on("click", function () {
                    self.getAll(false, self);

                    $(this).blur();
                    return false;
                });

                var d = new Date();

                $.getJSON(settings.url + "xml.php?json&" + d.getTime(), function (data) {
                    //Sysinfo
                    $("div.host").html(data.Vitals["@attributes"].Hostname + " (" + data.Vitals["@attributes"].IPAddr + ")");

                    $("div.distro span").html(data.Vitals["@attributes"].Distro);
                    $("div.distro div.icon").css("background-image", "url('" + settings.url + "gfx/images/" + data.Vitals["@attributes"].Distroicon + "')");

                    $("div.kernel").html(data.Vitals["@attributes"].Kernel);
                    $(".cpu-model-label").html(data.Hardware.CPU.CpuCore[0]["@attributes"].Model);
                    $("div.motherboard").html(data.Hardware["@attributes"].Name);

                    var date = new Date();
                    date.setSeconds(date.getSeconds() - Math.floor(data.Vitals["@attributes"].Uptime));

                    if ($("div.uptime").data("tinyTimer") != undefined) {
                        clearInterval($("div.uptime").data("tinyTimer").interval);
                    }
                    $("div.uptime").tinyTimer({ from: date, format: "%d days %0h:%0m:%0s" });

                    //Get CPU cores
                    $("li.cpu-cores:gt(0)").remove();

                    $.each(data.Hardware.CPU.CpuCore, function (index, value) {
                        var coreLabel = data.MBInfo.Temperature.Item[index]["@attributes"].Label;
                        var coreTemp = data.MBInfo.Temperature.Item[index]["@attributes"].Value + " &deg;" + data.Options["@attributes"].tempFormat.toUpperCase()
                        var coreVCore = 0;
                        var coreSpeedCurrent = (Math.round(value["@attributes"].CpuSpeed / 10) / 100) + " GHz";
                        var coreSpeedMin = (Math.round(value["@attributes"].CpuSpeedMin / 10) / 100) + " GHz";
                        var coreSpeedMax = (Math.round(value["@attributes"].CpuSpeedMax / 10) / 100) + " GHz";

                        $.each(data.MBInfo.Voltage.Item, function (index, value) {
                            if (value["@attributes"].Label == settings.vCore) {
                                coreVCore = value["@attributes"].Value;
                            }
                        });

                        var clone = $("li.cpu-cores:first").clone();
                        clone.removeClass("hidden_not_important");
                        clone.find(".cpu-core").html(coreLabel);
                        clone.find(".core-temp").html(coreTemp);
                        clone.find(".core-vcore").html(coreVCore + " V");
                        clone.find(".core-current").html(coreSpeedCurrent);
                        clone.find(".core-min").html(coreSpeedMin);
                        clone.find(".core-max").html(coreSpeedMax);
                        clone.insertAfter($("li.cpu-cores:last"));
                    });

                    //Todo: foreach network device
                    //Network
                    var rx = Math.round(data.Network.NetDevice[0]["@attributes"].RxBytes / 1024 / 1024 / 1024 * 100) / 100 + " GB";
                    var tx = Math.round(data.Network.NetDevice[0]["@attributes"].TxBytes / 1024 / 1024 / 1024 * 100) / 100 + " GB";
                    var info = data.Network.NetDevice[0]["@attributes"].Info.split(";");

                    $(".lan-name").html(data.Network.NetDevice[0]["@attributes"].Name);
                    $(".lan-mac").html(info[0]);
                    $(".lan-ip").html(info[1]);
                    $(".lan-speed").html(info[2]);
                    $(".lan-rx").html(rx);
                    $(".lan-tx").html(tx);

                    //Fans
                    $("li.fans .fan-stats > div:gt(0)").remove();
                    $.each(data.MBInfo.Fans.Item, function (index, value) {
                        var clone = $("li.fans .fan-stats div:first").clone();
                        clone.find(".name").html(value["@attributes"].Label);
                        clone.find(".value").html(value["@attributes"].Value + " RPM");
                        clone.appendTo($("li.fans .fan-stats"));
                    });

                    $("div.sysinfo .value").fadeIn();

                    //RAM
                    $("div.ram").find(".progress-bar").css("width", data.Memory["@attributes"].Percent + "%");
                    $("div.ram").find(".percent span").html(data.Memory["@attributes"].Percent);

                    //SWAP
                    if (data.Memory.Swap != undefined) {
                        $("div.swap").find(".progress-bar").css("width", data.Memory.Swap["@attributes"].Percent + "%");
                        $("div.swap").find(".percent span").html(data.Memory.Swap["@attributes"].Percent);
                    }
                    else {
                        $("div.swap").closest("li").hide();
                    }

                    //Disks
                    data.FileSystem.Mount.sort(function (a, b) {
                        return a["@attributes"].MountPoint < b["@attributes"].MountPoint ? -1 : 1;
                    });

                    $.each(data.FileSystem.Mount, function (index, value) {
                        var clone = $(".harddisks li:first-child").clone();
                        var percent = value["@attributes"].Percent + "%";

                        clone.find(".name").html(value["@attributes"].MountPoint);
                        clone.find(".progress-bar").css("width", percent);
                        clone.find(".percent").html(percent);
                        clone.removeClass("hidden");

                        if (value["@attributes"].Percent > 90) {
                            clone.find(".progress-bar").addClass("progress-bar-danger");
                        }
                        else if (value["@attributes"].Percent > 70) {
                            clone.find(".progress-bar").addClass("progress-bar-warning");
                        }
                        else if (value["@attributes"].Percent > 50) {
                            clone.find(".progress-bar").addClass("progress-bar-info");
                        }
                        else {
                            clone.find(".progress-bar").addClass("progress-bar-success");
                        }

                        $("div.harddisks li div:contains('" + value["@attributes"].MountPoint + "')").parent().remove();
                        clone.appendTo(".harddisks ul");
                    });
                    

                    $(".sysinfo .glyphicon-wrench").removeClass("disabled");

                    if (!onload) {
                        $(".sysinfo, .hardware, .harddisks").isLoading("hide");
                    }
                });

                self.updatenotifier();
            },

            psstatus: function (onload, self) {
                self = typeof self === 'undefined' ? this : self;
                onload = typeof onload === 'undefined' ? false : onload;

                if (!onload) {
                    $(".processes").isLoading({
                        text: "Loading",
                        position: "overlay"
                    });
                }

                settings.block.find(".glyphicon-refresh").off().on("click", function () {
                    self.psstatus(false, self);

                    $(this).blur();
                    return false;
                });

                var d = new Date();

                $.getJSON(settings.url + "xml.php?plugin=psstatus&json&" + d.getTime(), function (data) {
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

                        if (value["@attributes"].Status == 1) {
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

                    if (!onload) {
                        $(".processes").isLoading("hide");
                    }
                });
            },

            updatenotifier: function () {
                var d = new Date();

                $.getJSON(settings.url + "xml.php?plugin=updatenotifier&json&" + d.getTime(), function (data) {
                    if (data.Plugins.Plugin_UpdateNotifier != undefined) {
                        settings.block.find("span.packages").html("Packages:" + data.Plugins.Plugin_UpdateNotifier.UpdateNotifier.packages);
                        settings.block.find("span.security").html("Security:" + data.Plugins.Plugin_UpdateNotifier.UpdateNotifier.security);
                    }
                });
            }
        }
    }
})(jQuery);