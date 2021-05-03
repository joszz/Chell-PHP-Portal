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
    //override tabCollapse checkstate since we use 2 differen tabs for mobile and desktop view
    $.fn.tabCollapse.Constructor.prototype.checkState = function () {
        if (this._accordionVisible) {
            this.showTabs();
            this._accordionVisible = false;
        } else if (this.$accordion.is(':visible') && !this._accordionVisible) {
            this.showAccordion();
            this._accordionVisible = true;
        }
    };

    $(".nav-tabs.visible-xs").tabCollapse();

    //Set focus to correct tab when URL navigated to with location.hash
    if (location.hash) {
        $("a[href='" + location.hash + "']").tab("show");
    }

    $(".nav-tabs a").click(function () {
        location.hash = $(this).attr("href");
    });

    $(".fa-trash-alt").click(function () {
        openConfirmDialog("Delete this item?", [{ url: $(this).attr("href") }], function () {
            if ($(this).attr("id") === "confirm-yes") {
                window.location.href = $(this).closest("div").data("url");
            }

            $.fancybox.close();
        });

        return false;
    });

    $("#bgcolor").on("change", function () {
        if ($(this).val() == "timebg") {
            $(this).parents('.row').next().removeClass('hidden').next().removeClass('hidden');
        }
        else {
            $(this).parents('.row').next().addClass('hidden').next().addClass('hidden');
        }
    });

    $("input[type='number'][step!='any']").TouchSpin({
        verticalupclass: "fa fa-chevron-left",
        verticaldownclass: "fa fa-chevron-right",
        buttondown_class: "btn btn-default",
        buttonup_class: "btn btn-default",
        max: 1000000000
    });

    $("legend input[type='checkbox']").each(function () {
        toggleFieldsInFieldSet($(this));

        $(this).change(function () {
            toggleFieldsInFieldSet($(this));
        });
    });

    $(".location").next().on("click", function () {
        var $this = $(this);
        navigator.geolocation.getCurrentPosition(function (position) {
            var input = $this.prev();

            if (input.hasClass("latitude")) {
                input.val(position.coords.latitude);
            }
            else if (input.hasClass("longitude")) {
                input.val(position.coords.longitude);
            }
        });
    })

    $("#settings").fadeIn("fast");

    $(".webauth").on("click", function () {
        var userId = $.trim($(this).parents("tr").find(".id").html());

        $.getJSON("webauthchallenge/" + userId, function (challengeData) {
            webauthnRegister(challengeData, function (success, registrationData) {
                if (success) {
                    $.ajax({
                        url: "webauthregister",
                        method: "POST",
                        data: {
                            userid: userId,
                            registrationdata: registrationData
                        },
                        success: function (success) {
                            if (success) {
                                showAlert("success", "Authentication added");
                            }
                            else {
                                showAlert("danger", "Authentication failed to add");
                            }
                        },
                        error: function () {
                            showAlert("danger", "Authentication failed to add");
                        }
                    });
                } else {
                    showAlert("danger", "Authentication failed to add");
                }
            });
        });

        return false;
    });
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
