"use strict";

/**
* Adds the ability to show the text of password fields. Toggles from showing to hiding.
*
* @class Window
* @module General
*/
(function ($) {
    $.fn.togglePasswords = function () {
        this.on("click", function () {
            var input = $(this).parent().find("input");
            var currentType = input.attr("type");

            if (currentType == "password") {
                input.attr("type", "text");
            }
            else {
                input.attr("type", "password");
            }
        });
    };
})(jQuery);