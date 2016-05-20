var alertTimeout;
var altPressed = false;

$(function () {
    alertTimeout = $(".alert").data("alert-timeout");

    $.fancybox.defaults.margin = [70, 20, 60, 20];
    $("select").selectpicker({ width: "100%", container: "body" });

    Waves.attach(".btn, button, div#navbar a");
    Waves.init();

    initializeGlobalEventHandlers();
});

function initializeGlobalEventHandlers() {
    $(".fancybox").fancybox({
        afterLoad: function () {
            $.extend(this, {
                maxWidth: this.element.data("fancybox-maxwidth")
            })
        },
        helpers: {
            overlay: {
                locked: true
            }
        },
    });

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

function fadeOutAlert() {
    window.setTimeout(function () {
        $("div.alert").fadeOut("fast", function () {
            $("div.alert").removeClass("alert-success alert-danger");
        });
    }, alertTimeout * 1000);
}

String.prototype.capitalize = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
}