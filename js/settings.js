"use strict";

/**
* Main entry point for settings view.
*
* @class Index
* @module Settings
*/


initializePlugins()
setEventHandlers();
setSelectData($("select[name='pulseway-systems[]']"));
setSelectData($("select[name='jellyfin-views[]']"));
setSelectData($("select[name='sonos-household_id']"), function () {
    setSelectData($("select[name='sonos-group_id']"));
});

$("legend input[type='checkbox']").each(function () {
    toggleFieldsInFieldSet($(this));

    $(this).change(function () {
        toggleFieldsInFieldSet($(this));
    });
});

setTimeout(function () {
    $("#settings > .panel-body").fadeIn("fast");
}, 0);

function initializePlugins() {
    scrollToError();
}

function setEventHandlers() {
    $(".fa-trash-can").click(function () {
        openConfirmDialog("Delete this item?", [{ url: $(this).attr("href") }], function () {
            if ($(this).attr("id") === "confirm-yes") {
                window.location.href = $(this).closest("div").data("url");
            }

            $.fancybox.close();
        });

        return false;
    });

    $("#application-background").on("change", function () {
        if ($(this).val() == "timebg") {
            $(this).parents('.row').next().removeClass('hidden').next().removeClass('hidden');
        }
        else {
            $(this).parents('.row').next().addClass('hidden').next().addClass('hidden');
        }
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
    });

    $(".refresh-api").on("click", function () {
        setSelectData($(this).parents(".input-group").find("select"));
        return false;
    });

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

    $("#settings > .panel-heading select").on("change", function () {
        window.location.href = $("body").data("baseuri") + "settings/" + $(this).val();
    });
}

function setSelectData(select, callback) {
    if (select.length) {
        var data = {};
        var selected = select.data("selected").split(",");
        select.find("option").remove();
        select.attr('disabled', true);
        select.selectpicker("refresh");

        select.parents("fieldset").find("input, select").each((index, input) => {
            data[$(input).attr("name")] = $(input).val();
        });

        $.ajax({
            url: select.data("apiurl"),
            method: "POST",
            dataType: "json",
            data: data,
            success: function (data) {
                $.each(data, function (index, value) {
                    select.append("<option value='" + index + "' " + (selected.indexOf(index) !== -1 ? "selected" : "") + ">" + value + "</option>");
                });

                select.attr('disabled', false);
                select.selectpicker("refresh");

                if (callback) {
                    callback();
                }
            }
        });
    }
}

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

function scrollToError() {
    var scrollTo = $("#settings").data("scrollto");

    if (scrollTo) {
        setTimeout(function () {
            $([document.documentElement, document.body]).animate({
                scrollTop: $("input[name='" + scrollTo + "']").offset().top - 60
            });
        }, 0)
    }
}