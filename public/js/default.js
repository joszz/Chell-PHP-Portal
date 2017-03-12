/**
* Main entry point for all views.
* 
* @class Window
* @module General
*/

/**
* The time to show notifications, defined in config.ini.
* 
* @property alertTimeout
* @type Number
*/
var alertTimeout;

/**
* Keeping track if the alt key has been pressed.
* 
* @property altPressed
* @type Boolean
*/
var altPressed = false;

/**
* Document onload, call to initialize plugins and eventhandlers.
* 
* @method document.onload
*/
$(function () {
    alertTimeout = $(".alert").data("alert-timeout");

    initializeGlobalPlugins();
    initializeGlobalEventHandlers();
});

/**
* Initializes all globally used plugins.
* 
* @method initializeGlobalPlugins
*/
function initializeGlobalPlugins() {
    $("a, button, h4").vibrate();

    $("select").selectpicker({ width: "100%", container: "body" });

    Waves.attach(".btn, button, div#navbar a");
    Waves.init();

    $.fancybox.defaults.smallBtn = $.fancybox.defaults.fullScreen = $.fancybox.defaults.slideShow = false;
    $.fancybox.defaults.iframe.css = {
        "max-width": "800px",
        "width": "90%"
    };
}

/**
* Initialized all globally used eventhandlers.
* 
* @method initializeGlobalEventHandlers
*/
function initializeGlobalEventHandlers() {
    $(".navbar-toggle").click(function (event) {
        event.stopImmediatePropagation();
        $(".navbar-collapse").slideToggle("fast");
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

/**
* Sets a timeout to fadeout the alert, uses alertTimeout (defined in config.ini) to determine the timeout
* 
* @method fadeOutAlert
*/
function fadeOutAlert() {
    window.setTimeout(function () {
        $("div.alert").fadeOut("fast", function () {
            $("div.alert").removeClass("alert-success alert-danger");
        });
    }, alertTimeout * 1000);
}

/**
* Capitalizes the first character of a string, as prototype so it can be used on strings.
* 
* @method capitalize
*/
String.prototype.capitalize = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
}