var config;
var checkDeviceStatesIntervalId, rotateMoviesIntervalId, rotateAlbumsIntervalId, rotateEpisodesIntervalId;
var checkDeviceStatesInterval;
var transmission;

$(function () {
    checkDeviceStatesInterval = $(".devices").data("device-state-interval");

    initializeDashboardEventHandlers();

    $(".sysinfo, #hardware").phpsysinfo().getAll(true);
    $(".processes").phpsysinfo().psstatus(true);
    $(".sysinfo.update").phpsysinfo().updatenotifier();

    checkDeviceStates();
    checkDeviceStatesIntervalId = setInterval(checkDeviceStates, checkDeviceStatesInterval * 1000);

    transmission = $(".transmission").transmission().getTorrents(true);

    initGallery("movies", rotateMoviesIntervalId);
    initGallery("episodes", rotateEpisodesIntervalId);
    initGallery("albums", rotateAlbumsIntervalId);
});

function initializeDashboardEventHandlers() {
    $("div.devices").on("click", "a.btn-danger", openWolDialog);
    $("div#wol-dialog button").click(wol);
    $("div.devices").on("click", "a.btn-success", openShutdownDialog);
    $("div#shutdown-dialog button").click(doShutdown);

    $("div.devices div.panel-heading a.glyphicon-refresh").click(function () {
        clearInterval(checkDeviceStatesIntervalId);
        checkDeviceStates();
        checkDeviceStatesIntervalId = setInterval(checkDeviceStates, checkDeviceStatesInterval * 1000);

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

    $("div.transmission a.glyphicon-refresh").click(function () {
        transmission.getTorrents();

        $(this).blur();
        return false;
    });

    $("a.toggle").click(function () {
        $(this).toggleClass("glyphicon-minus glyphicon-plus");
        $(this).closest(".panel").find(".list-group, .panel-body").slideToggle("fast");

        $(this).blur();
        return false;
    });
}

function initGallery(which, intervalId) {
    intervalId = setInterval(function () {
        rotateGallery(which, "right");
    }, $("div." + which).data("rotate-interval") * 1000);

    $("div." + which + " .glyphicon-chevron-left, div." + which + " .glyphicon-chevron-right").click(function () {
        clearInterval(intervalId);
        rotateGallery(which, $(this).hasClass("glyphicon-chevron-left") ? "left" : "right");

        intervalId = setInterval(function () {
            rotateGallery(which, "right");
        }, $("div." + which).data("rotate-interval") * 1000);

        $(this).blur();
        return false;
    });
}

function rotateGallery(which, direction) {
    var parent = $("div." + which);
    var currentIndex = parent.find("div.item:visible").index();
    var offset = direction == "right" ? 1 : -1;

    nextIndex = parent.find("div.item:eq(" + (currentIndex + offset) + ")").length == 1 ? currentIndex + offset : 0;

    if (currentIndex != nextIndex) {
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

        if (data == "true") {
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