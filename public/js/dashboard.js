var checkDeviceStatesIntervalId, rotateMoviesInterval, rotateAlbumsInterval;
var config;

$(function () {
    $.getJSON("index/dashboardsettings", function (data) {
        config = data;

        initializeDashboardEventHandlers();
        getPHPSysInfo(true);
        getPHPSysInfoPSStatus(true);
        
        getPHPSysInfoUpdateNotifier();
        checkDeviceStates();
        checkDeviceStatesIntervalId = setInterval(checkDeviceStates, config.checkDeviceStatesTimeout * 1000);

        initGallery("movies");
        initGallery("episodes");
        initGallery("albums");

        $("a.toggle").click(function () {
            $(this).toggleClass("glyphicon-minus glyphicon-plus");
            $(this).closest(".panel").find(".list-group, .panel-body").slideToggle("fast");

            $(this).blur();
            return false;
        });
    });
});

function initializeDashboardEventHandlers() {
    $("div.devices").on("click", "a.btn-danger", openWolDialog);
    $("div#wol-dialog button").click(wol);
    $("div.devices").on("click", "a.btn-success", openShutdownDialog);
    $("div#shutdown-dialog button").click(doShutdown);

    $("div.devices div.panel-heading a.glyphicon-refresh").click(function () {
        clearInterval(checkDeviceStatesIntervalId);
        checkDeviceStates();
        checkDeviceStatesIntervalId = setInterval(checkDeviceStates, config.checkDeviceStatesTimeout * 1000);

        $(this).blur();
        return false;
    });

    $("div.processes a.glyphicon-refresh").click(function () {
        getPHPSysInfoPSStatus();

        $(this).blur();
        return false;
    });

    $("div.sysinfo a.glyphicon-refresh, div#hardware a.glyphicon-refresh").click(function () {
        getPHPSysInfo();
        getPHPSysInfoUpdateNotifier();

        $(this).blur();
        return false;
    });
}

function initGallery(which) {
    window["rotate" + which.capitalize() + "Interval"] = setInterval(function () {
        rotateGallery(which, "right");
    }, window["config"]["rotate" + which.capitalize() + "Timeout"] * 1000);

    $("div." + which + " .glyphicon-chevron-left, div." + which + " .glyphicon-chevron-right").click(function () {
        clearInterval(window["rotate" + which.capitalize() + "Interval"]);
        rotateGallery(which, $(this).hasClass("glyphicon-chevron-left") ? "left" : "right");

        window["rotate" + which.capitalize() + "Interval"] = setInterval(function () {
            rotateGallery(which, "right");
        }, window["config"]["rotate" + which.capitalize() + "Timeout"] * 1000);

        $(this).blur();
        return false;
    });
}

function rotateGallery(which, direction) {
    var parent = $("div." + which);
    var currentIndex = parent.find("div.item:visible").index();
    var offset = direction == "right" ? 1 : -1;

    nextIndex = parent.find("div.item:eq(" + (currentIndex + offset) + ")").length == 1 ? currentIndex + offset : 0;

    if (currentIndex != nextIndex){
        parent.find("div.item:eq(" + currentIndex + ")").fadeOut("fast", function () {
            parent.find("div.item:eq(" + nextIndex + ")").fadeIn("fast");
        });
    }
}

function openWolDialog() {
    $(this).blur();
    var name = $(this).closest("li").find("div:first").html().trim();

    $("div#wol-dialog h2 span").html(name);
    $("div#wol-dialog input[name='mac']").val($(this).data("mac"));

    $.fancybox({
        content: $("div#wol-dialog").show(),
        closeBtn: false,
        closeClick: false,
        helpers: {
            overlay: {
                closeClick: false,
                locked: true
            }
        },
    });
}

function wol() {
    $.fancybox.close();

    if ($(this).attr("id") == "wol-yes") {
        var name = $(this).closest("div").find("h2 span").html().trim();

        $.get("devices/wol?mac=" + $(this).closest("div").find("input[name='mac']").val(), function (name) {
            clearTimeout(alertIntervalId);

            $("div.alert").addClass("alert-success");
            $("div.alert").html("Magic packet send to: " + name);
            $("div.alert").fadeIn("fast");

            fadeOutAlert();
        }(name));
    }
}

function openShutdownDialog() {
    $(this).blur();
    var name = $(this).closest("li").find("div:first").html().trim();

    $("div#shutdown-dialog h2 span").html(name);
    $("div#shutdown-dialog input[name='ip']").val($(this).data("ip"));

    $.fancybox({
        content: $("div#shutdown-dialog").show(),
        afterShow: function () {
            $("div#shutdown-dialog input:first").focus();
        },
        helpers: {
            overlay: {
                locked: true
            }
        }
    });
}

function doShutdown() {
    var user = $("div#shutdown-dialog input[name='user']").val();
    var password = $("div#shutdown-dialog input[name='password']").val();
    var ip = $("div#shutdown-dialog input[name='ip']").val();
    var name = $("div#shutdown-dialog h2 span").html();

    $.get("devices/shutdown?ip=" + ip + "&user=" + user + " &password=" + password, function (data) {
        $.fancybox.close();
        clearTimeout(alertIntervalId);

        if(data == "true") {
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

    return false;
}

function checkDeviceStates() {
    var d = new Date();

    $("div.devices a.devicestate").each(function () {
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
}

function getPHPSysInfo(onload) {
    onload = typeof onload === 'undefined' ? false : onload;

    if(!onload) {
        $(".sysinfo, #hardware").isLoading({
            text: "Loading",
            position: "overlay"
        });
    }

    var d = new Date();

    $.getJSON(config.phpSysInfoURL + "xml.php?json&" + d.getTime(), function (data) {
        //Sysinfo
        $("div.host").html(data.Vitals["@attributes"].Hostname + " (" + data.Vitals["@attributes"].IPAddr + ")");

        $("div.distro span").html(data.Vitals["@attributes"].Distro);
        $("div.distro div.icon").css("background-image", "url('" + config.phpSysInfoURL + "gfx/images/" + data.Vitals["@attributes"].Distroicon + "')");

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
                if (value["@attributes"].Label == config.phpSysInfoVCore) {
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
        var rx = Math.round(data.Network.NetDevice[0]["@attributes"].RxBytes / 1024 / 1024 / 1024 * 100) / 100 + " GB";
        var tx = Math.round(data.Network.NetDevice[0]["@attributes"].TxBytes / 1024 / 1024 / 1024 * 100) / 100 + " GB";
        var info = data.Network.NetDevice[0]["@attributes"].Info.split(";");

        $(".lan-name").html(data.Network.NetDevice[0]["@attributes"].Name);
        $(".lan-mac").html(info[0]);
        $(".lan-ip").html(info[1]);
        $(".lan-speed").html(info[2]);
        $(".lan-rx").html(rx);
        $(".lan-tx").html(tx);

        $("li.fans .fan-stats > div:gt(0)").remove();
        $.each(data.MBInfo.Fans.Item, function (index, value) {
            var clone = $("li.fans .fan-stats div:first").clone();
            clone.find(".name").html(value["@attributes"].Label);
            clone.find(".value").html(value["@attributes"].Value + " RPM");
            clone.appendTo($("li.fans .fan-stats"));
        });

        $("div.sysinfo .value").fadeIn();

        $("div.ram").find(".progress-bar").css("width", data.Memory["@attributes"].Percent + "%");
        $("div.ram").find(".percent span").html(data.Memory["@attributes"].Percent);

        if (data.Memory.Swap != undefined){
            $("div.swap").find(".progress-bar").css("width", data.Memory.Swap["@attributes"].Percent + "%");
            $("div.swap").find(".percent span").html(data.Memory.Swap["@attributes"].Percent);
        }
        else {
            $("div.swap").closest("li").hide();
        }

        $(".sysinfo .glyphicon-wrench").removeClass("disabled");

        if (!onload) {
            $(".sysinfo, #hardware").isLoading("hide")
        }
    });
}

function getPHPSysInfoPSStatus(onload) {
    onload = typeof onload === 'undefined' ? false : onload;

    if (!onload) {
        $(".processes").isLoading({
            text: "Loading",
            position: "overlay"
        });
    }

    var d = new Date();

    $.getJSON(config.phpSysInfoURL + "xml.php?plugin=psstatus&json&" + d.getTime(), function (data) {
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
}

function getPHPSysInfoUpdateNotifier() {
    var d = new Date();

    $.getJSON(config.phpSysInfoURL + "xml.php?plugin=updatenotifier&json&" + d.getTime(), function (data) {
        if (data.Plugins.Plugin_UpdateNotifier != undefined) {
            $("span.update-packages").html("Packages:" + data.Plugins.Plugin_UpdateNotifier.UpdateNotifier.packages);
            $("span.update-security").html("Security:" + data.Plugins.Plugin_UpdateNotifier.UpdateNotifier.security);
        }
    });
}
