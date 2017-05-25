"use strict";

/**
* Main entry point for settings view.
* 
* @class Index
* @module Settings
*/

/**
* Document onload, sets up setting view specific eventhandlers.
* 
* @method document.onload
*/
$(function () {
    $(".nav-tabs").tabCollapse();

    //Set focus to correct tab when URL navigated to with location.hash
    if (location.hash) {
        $("a[href='" + location.hash + "']").tab("show");
    }

    $(".nav-tabs a").click(function () {
        location.hash = $(this).attr("href");
    });

    $(".fa-remove").click(function () {
        openConfirmDialog("Delete this item?", [{ url: $(this).attr("href") }], function () {
            if ($(this).attr("id") === "confirm-yes") {
                window.location.href = $(this).closest("div").data("url");
            }

            $.fancybox.close();
        });

        return false;
    });

    $("input[type='number']").TouchSpin({
        verticalupclass: "fa fa-chevron-left",
        verticaldownclass: "fa fa-chevron-right"
    });

    $("input[type='checkbox']").each(function () {
        toggleFieldsInFieldSet($(this));

        $(this).change(function () {
            toggleFieldsInFieldSet($(this));
        });
    });

    $("legend").click(function (event) {
        if (event.target.nodeName.toLowerCase() === "legend") {
            var state = $(this).find("input").prop("checked");

            $(this).find("input").prop("checked", !state).change();
        }
    });

    $("#main-settings").fadeIn("fast");
});

/**
 * Collapses/expands all fields that are hidden by default/rendertime in fieldsets.
 * 
 * @method toggleFieldsInFieldSet
 * @param {Object}  $this   The reference to the checkbox being toggled.
 */
function toggleFieldsInFieldSet($this) {
    var elements = $this.closest("fieldset").find(".form-group");

    if ($this.prop("checked")) {
        elements.removeClass("hidden");
    }
    else {
        elements.addClass("hidden");
    }
}