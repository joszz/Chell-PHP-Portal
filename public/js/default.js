var checkDeviceStatesIntervalId, alertIntervalId;
var altPressed = false;
var config;

$(function () {
    $.fancybox.defaults.margin = [70, 0, 60, 0];
    
    $.get("index/dashboardsettings", function (data) {
        config = $.parseJSON(data);
        
        initializeEventHandlers();
        getPHPSysInfo();
        checkDeviceStates();
        checkDeviceStatesIntervalId = setInterval(checkDeviceStates, config.checkDeviceStatesTimeout * 1000);
    });
});

function initializeEventHandlers() {
    $("a.fancybox.iframe").fancybox({ type: "iframe" });
    $(".shorten").shorten();
    $("a, button").vibrate();

    $("div.devices").on("click", "a.btn-danger", wol);
    $("div.devices").on("click", "a.btn-success", openShutdownDialog);
    $("div.shutdown input[type='submit']").click(doShutdown);

    $("div.devices div.panel-heading a.glyphicon-refresh").click(function () {
        clearInterval(checkDeviceStatesIntervalId);
        checkDeviceStates();
        checkDeviceStatesIntervalId = setInterval(checkDeviceStates, config.checkDeviceStatesTimeout * 1000);

        return false;
    });

    $("ul.nav li.disabled a").click(function () {
        return false;
    });

    $("body").keydown(function (e) {
        if (altPressed) {
            if (e.which == 77 && altPressed) {
                altPressed = false;
                $(".navbar-toggle").trigger("click");
            }
            //number pressed
            if (e.which > 48 && e.which < 58) {
                $("ul.nav li").eq(e.which - 49).find("a")[0].click();
            }
        }
        else if (e.which == 18) altPressed = true;
    });

    $("body").keyup(function (e) {
        if (e.which == 18) altPressed = false;
    });
}

function wol() {
    var name = $(this).closest("li").find("div:first").html().trim();

    if (confirm("Are you sure you want to wake " + name + "?")) {
        $.get("devices/wol?mac=" + $(this).data("mac"), function (name) {
            clearTimeout(alertIntervalId);

            $("div.alert").addClass("alert-success");
            $("div.alert").html("Magic packet send to: " + name);
            $("div.alert").fadeIn("fast");

            fadeOutAlert();
        }(name));
    }
}

function openShutdownDialog() {
    var name = $(this).closest("li").find("div:first").html().trim();

    $("div.shutdown h2 span").html(name);
    $("div.shutdown input[name='ip']").val($(this).data("ip"));

    $.fancybox({
        content: $("div.shutdown").show(),
        afterShow: function () {
            $("div.shutdown input:first").focus();
        }
    });
}

function doShutdown() {
    user        = $("div.shutdown input[name='user']").val();
    password    = $("div.shutdown input[name='password']").val();
    ip          = $("div.shutdown input[name='ip']").val();
    name        = $("div.shutdown h2 span").html();

    $.get("devices/shutdown?ip=" + ip + "&user=" + user + " &password=" + password, function (name) {
        $.fancybox.close();
        clearTimeout(alertIntervalId);

        $("div.alert").addClass("alert-success");
        $("div.alert").html("Shutdown command send to: " + name);
        $("div.alert").fadeIn("fast");

        fadeOutAlert();
    }(name));

    return false;
}

function checkDeviceStates() {
    var d = new Date();

    $("div.devices a.devicestate").each(function () {
        var device              = $(this);
        var icon                = $(this).find("span.glyphicon");
        var ip                  = $(this).data("ip");
        var dependentMenuItems  = $("ul.nav li[data-ip='" + ip + "'");

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
                }

                device.addClass("btn-" + (data["state"] ? "success" : "danger"));

            });
        }(device, dependentMenuItems, icon, ip));
    });
}

function getPHPSysInfo() {
    $.get(config.phpSysInfoURL + "xml.php?plugin=complete&json", function (data) {
        data = $.parseJSON(data);

        //Sysinfo
        $("div.host").html(data.Vitals["@attributes"].Hostname + " (" + data.Vitals["@attributes"].IPAddr + ")");

        $("div.distro span").html(data.Vitals["@attributes"].Distro);
        $("div.distro div.icon").css("background-image", "url('/sysinfo/gfx/images/" + data.Vitals["@attributes"].Distroicon +"')");

        $("div.kernel").html(data.Vitals["@attributes"].Kernel);

        $("span.update-packages").html("Packages:" + data.Plugins.Plugin_UpdateNotifier.UpdateNotifier.packages);
        $("span.update-security").html("Security:" + data.Plugins.Plugin_UpdateNotifier.UpdateNotifier.security);
        
        $("a#cpu-model-label").html(data.Hardware.CPU.CpuCore[0]["@attributes"].Model);
        $("a#cpu-model-label").click(function () {
            $("div#cpu-cores").slideToggle();
            return false;
        });

        $("div.motherboard").html(data.Hardware["@attributes"].Name);

        $("div.motherboard, div.kernel, a#cpu-model-label").shorten({
            width: 200,
            tail: '...',
            tooltip: true
        });

        var seconds = Math.floor(data.Vitals["@attributes"].Uptime);
        var minutes = Math.floor(seconds / 60);
        var hours = Math.floor(minutes / 60);
        var days = Math.floor(hours / 24);

        hours = ("0" + (hours - (days * 24))).slice(-2);
        minutes = ("0" + (minutes - (days * 24 * 60) - (hours * 60))).slice(-2);
        seconds = ("0" + (seconds - (days * 24 * 60 * 60) - (hours * 60 * 60) - (minutes * 60))).slice(-2);
        $("div.uptime").html(days + " days " + hours + ":" + minutes + ":" + seconds);

        //Get processes
        $.each(data.Plugins.Plugin_PSStatus.Process, function (index, value) {
            var listItem = $("<li />", { class: "list-group-item col-xs-12 col-md-12" });
            var name = $("<div />", { class: "col-xs-10" });
            var status = $("<div />", { class: "col-xs-2" });
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
            listItem.appendTo($("div.processes ul"));
        });
        
        //Get CPU cores
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

            clone = $("div#cpu-cores div:first").clone();
            clone.find(".cpu-core").html(coreLabel);
            clone.find(".core-temp").html("Temp: " + coreTemp);
            clone.find(".core-vcore").html("vCore: " + coreVCore + " V");
            clone.find(".core-current").html("Current: " + coreSpeedCurrent);
            clone.find(".core-min").html("Min: " +  coreSpeedMin);
            clone.find(".core-max").html("Max: " + coreSpeedMax);
            clone.click(function () {
                $(this).find(".core-stats").slideToggle();
                return false;
            });
            clone.appendTo($("div#cpu-cores"));
        });

        $("div.sysinfo .value").fadeIn();
        $("div.processes ul").slideDown();

        $("div.ram").find(".progress-bar").css("width", data.Memory["@attributes"].Percent + "%");
        $("div.ram").find(".percent span").html(data.Memory["@attributes"].Percent);

        $("div.swap").find(".progress-bar").css("width", data.Memory.Swap["@attributes"].Percent + "%");
        $("div.swap").find(".percent span").html(data.Memory.Swap["@attributes"].Percent);
    });
}

function fadeOutAlert() {
    alertIntervalId = window.setTimeout(function () {
        $("div.alert").fadeOut("fast", function () {
            $("div.alert").removeClass("alert-success alert-danger");
        });
    }, config.alertTimeout * 1000);
}