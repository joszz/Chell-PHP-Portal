"use strict";

/**
* Main entry point for all views.
*
* @class Window
* @module General
*/

var keys = {
    Enter: 13, Alt: 18, M: 77,
    Number1: 49, Number9: 57,
    ArrowLeft: 37, ArrowUp: 38, ArrowRight: 39, ArrowDown: 40
};

var baseUri = $("body").data("baseuri");
var diskspaceunits = ["B", "KB", "MB", "GB", "TB"];
var alertTimeout = $(".alert").data("alert-timeout");

/**
* Document onload, call to initialize plugins and eventhandlers.
*
* @method document.onload
*/
if ("serviceWorker" in navigator) {
    if (baseUri) {
        window.addEventListener("load", function () {
            navigator.serviceWorker.register(baseUri + "index/worker", { scope: baseUri }).then(function (_registration) {
                // Registration was successful
            }, function (_err) {
                // registration failed :(
            });
        });
    }
}

initializeGlobalPlugins();
initializeGlobalEventHandlers();

var iframe = $("body#iframe");
if (iframe.length && iframe.data("bgImage").length) {
    iframe.css("background-image", "url('" + iframe.data("bgImage") + "')")
}

/**
* Initializes all globally used plugins.
*
* @method initializeGlobalPlugins
*/
function initializeGlobalPlugins() {
    $.ajaxSetup({ cache: false });

    $("a, button, h4").vibrate("short");

    Waves.attach(".btn, button, div#navbar a");
    Waves.init();

    if ($.fn.selectpicker) {
        $("select:not(.no-selectpicker)").selectpicker({ width: "100%", container: "body", showTick: true, tickIcon: "fa-check", iconBase: "fa" });
    }
    if ($.fn.TouchSpin) {
        $("input[type='number'][step!='any']").TouchSpin({
            verticalupclass: "fa fa-chevron-left",
            verticaldownclass: "fa fa-chevron-right",
            buttondown_class: "btn btn-default",
            buttonup_class: "btn btn-default",
            max: Number.MAX_SAFE_INTEGER
        });
    }
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
            if (e.which >= keys.Number1 && e.which <= keys.Number9) {
                $("ul.nav li").eq(e.which - 49).find("a")[0].click();
            }
        }
    });

    $("body").on(".disabled", "click", function () {
        return false;
    });

    $("body").on("click", function (e) {
        var menu_opened = $('#navbar').is(':visible');

        if (!$(e.target).closest("nav.navbar").length && !$(e.target).is("nav.navbar") && menu_opened) {
            $(".navbar-collapse").slideUp("fast");
        }
    });

    $("footer .fa-expand").click(function () {
        $(this).toggleClass("fa-expand fa-compress");
        $(document).fullScreen(!$(document).fullScreen());
    });
}

function initializeTooltip() {
    $("body").tooltip("destroy");
    $("body").tooltip({ selector: ".bs-tooltip", container: "body" });
}
/**
 * Shows the alert box with the message provided.. Alerttype is a bootstrap type (success, danger etc)
 *
 * @method showAlert
 * @param {String} alertType    The bootstrap type.
 * @param {String} message      The message to show.
 */
function showAlert(alertType, message) {
    $("div.alert").addClass("alert-" + alertType).html(message).fadeIn("fast");
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

    $.each(data, function (_index, value) {
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

function isLoggedIn(callBackOnLoggedOut) {
    $.getJSON(baseUri + "session/isLoggedIn", function (loggedIn) {
        if (!loggedIn) {
            callBackOnLoggedOut();
        }
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

/**
* Given a number representing a memory size, format it to a more readable size (MB/GB/TB depending on the size of the number).
*
* @method getFormattedSize
* @param {number} number    The number to format.
*/
function getFormattedSize(number) {
    var iteration = 0;

    while (number > 1024) {
        number /= 1024;
        iteration++;
    }

    return (Math.round(number * 100) / 100) + " " + diskspaceunits[iteration];
}