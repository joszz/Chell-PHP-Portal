var checkDeviceStatesTimeout = 30, alertTimeout = 5;
var checkDeviceStatesIntervalId, alertIntervalId;
var altPressed = false;

$(function () {
    initializeEventHandlers();

    checkDeviceStates();
    checkDeviceStatesIntervalId = setInterval(checkDeviceStates, checkDeviceStatesTimeout * 1000);
});

function initializeEventHandlers() {
    $("div.devices").on("click", "a.btn-danger", wol);
    $("div.devices div.panel-heading a.glyphicon-refresh").click(function () {
        clearInterval(checkDeviceStatesIntervalId);
        checkDeviceStates();
        checkDeviceStatesIntervalId = setInterval(checkDeviceStates, checkDeviceStatesTimeout * 1000);

        return false;
    });

    $("a.fancybox.iframe").fancybox({ type: "iframe" });
    $(".shorten").shorten();
    $("a, button").vibrate();

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

function checkDeviceStates() {
    var d = new Date();

    $("div.devices a.devicestate").each(function () {
        var device = $(this);
        var icon = $(this).find("span.glyphicon");
        var ip = $(this).data("ip");
        var dependentMenuItems = $("ul.nav li[data-ip='" + ip + "'");

        $(this).removeClass("btn-danger btn-success");
        $(this).addClass("disabled");

        icon.removeClass("glyphicon-off");
        icon.addClass("glyphicon-refresh icon-refresh-animate");

        (function (device, dependentMenuItems, icon, ip) {
            $.getJSON("devices/state?ip=" + ip + "&" + d.getTime(), "", function (data) {
                icon.removeClass("glyphicon-refresh icon-refresh-animate");
                icon.addClass("glyphicon-off");

                if (!data["state"]) {
                    device.removeClass("disabled");
                    dependentMenuItems.addClass("disabled");
                }
                else {
                    device.addClass("disabled");
                    dependentMenuItems.removeClass("disabled");
                }

                device.addClass("btn-" + (data["state"] ? "success" : "danger"));

            });
        }(device, dependentMenuItems, icon, ip));
    });
}

function fadeOutAlert() {
    alertIntervalId = window.setTimeout(function () {
        $("div.alert").fadeOut("fast", function () {
            $("div.alert").removeClass("alert-success alert-danger");
        });
    }, alertTimeout * 1000);
}