"use strict";

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
 * Enum with used keyboard shortcut codes
 * 
 * @property keys
 * @type Object
 */
var keys = {
    Enter: 13, Alt: 18, M: 77,
    Number1: 49, Numver9: 57,
    ArrowLeft: 37, ArrowUp: 38, ArrowRight: 39, ArrowDown: 40
};

/**
* Document onload, call to initialize plugins and eventhandlers.
* 
* @method document.onload
*/
$(function () {
    alertTimeout = $(".alert").data("alert-timeout");

    initializeGlobalPlugins();
    initializeGlobalEventHandlers();

    $("#duo_iframe").on('load', function () {
        $(this).fadeIn();
    });
});

/**
* Initializes all globally used plugins.
* 
* @method initializeGlobalPlugins
*/
function initializeGlobalPlugins() {
    $("a, button, h4").vibrate("short");

    $("select").selectpicker({ width: "100%", container: "body" });

    Waves.attach(".btn, button, div#navbar a");
    Waves.init();

    $.fancybox.defaults.smallBtn = $.fancybox.defaults.fullScreen = $.fancybox.defaults.slideShow = false;
    $.fancybox.defaults.buttons = ["close"];
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
        if (e.altKey) {
            if (e.which === keys.M) {
                keys.AltDown = false;
                $(".navbar-toggle").trigger("click");
            }
            //number pressed
            if (e.which >= keys.Number1 && e.which <= keys.Numver9) {
                $("ul.nav li").eq(e.which - 49).find("a")[0].click();
            }
        }
    });

    $("footer .fa-arrows-alt").click(function () {
        $(document).fullScreen(!$(document).fullScreen());
    });

    $("body").on("a.disabled", "click", function () {
        return false;
    });
}

/**
 * Shows the alert box with the message provided.. Alerttype is a bootstrap type (success, danger etc)
 * 
 * @method showAlert
 * @param {String} alertType    The bootstrap type.
 * @param {String} message      The message to show.
 */
function showAlert(alertType, message) {
    $("div.alert").addClass("alert-" + alertType);
    $("div.alert").html(message);
    $("div.alert").fadeIn("fast");
    fadeOutAlert();
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
* @returns {String}     The capitalized string.
*/
String.prototype.capitalize = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
};

/**
* Shows a confirm dialog with yes/no buttons
* 
* @method openConfirmDialog
* @param {String} title         The title to set for the confirm dialog.
* @param {String} data          The data attributes to set on the confirm dialog, for later us.
* @param {Object} buttonClick   callback for clicking the confirm button.
*/
function openConfirmDialog(title, data, buttonClick) {
    $("div#confirm-dialog h2").html(title);

    $.each(data, function (index, value) {
        $.each(value, function (index, value) {
            $("div#confirm-dialog").data(index, value);
        });
    });

    $("div#confirm-dialog button").off().on("click", buttonClick);

    $.fancybox.open({
        src: "#confirm-dialog",
        modal: true
    });
}

/**
 * Zeropads a string to match a string length of the given max.
 * 
 * @method zeropad
 * @param {String} str          The string to zeropad.
 * @param {max} max             The max length of the string.
 */
function zeropad(str, max) {
    str = str.toString();
    return str.length < max ? zeropad("0" + str, max) : str;
}