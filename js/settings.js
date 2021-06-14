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
    initializePlugins()
    setEventHandlers();
    setSelectData($("select[name='pulseway-systems[]']"));
    setSelectData($("select[name='jellyfin-views[]']"));
    
    $("legend input[type='checkbox']").each(function () {
        toggleFieldsInFieldSet($(this));

        $(this).change(function () {
            toggleFieldsInFieldSet($(this));
        });
    });

    $("#settings").fadeIn("fast");
});

function initializePlugins() {
    //override tabCollapse checkstate since we use 2 differen tabs for mobile and desktop view
    $.fn.tabCollapse.Constructor.prototype.checkState = function () {
        if ($(".nav-tabs.hidden-xs").is(":visible") && this._accordionVisible) {
            this.showTabs();
            this._accordionVisible = false;
        }
        else if (this.$accordion.is(":visible") && !this._accordionVisible) {
            this.showAccordion();
            this._accordionVisible = true;
        }
    };

    $(".nav-tabs.visible-xs").tabCollapse();

    //Set focus to correct tab when URL navigated to with location.hash
    if (location.hash) {
        $("a[href='" + location.hash + "']").tab("show");

        if ($(".tab-content " + location.hash + " form").length) {
            $("nav.navbar .fa-save").fadeIn().attr("form", "form_" + location.hash.replace("#", ""));
        }

        $("#settings .nav-tabs").find("a").on("shown.bs.tab", scrollToError);
    }
    else {
        scrollToError(0);
        var currentForm = $("form:visible");
        $("nav.navbar .fa-save").fadeIn().attr("form", currentForm.attr("id"));
    }
}

function setEventHandlers() {
    $(".nav-tabs.hidden-xs a").click(function () {
        var anchor = $(this).attr("href");
        location.hash = anchor;
    });

    $(".nav-tabs.hidden-xs a").click(function () {
        setTimeout(setSaveButton, 0);
    });

    $(".nav-tabs a").on("shown.bs.tab", setSaveButton);

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
}

function setSelectData(select) {
    if (select.length) {
        var selected = select.data("selected").split(",");
        select.find("option").remove();
        select.attr('disabled', true);
        select.selectpicker("refresh");

        $.ajax({
            url: select.data("apiurl"),
            dataType: "json",
            success: function (data) {
                $.each(data, function (index, value) {
                    select.append("<option value='" + index + "' " + (selected.indexOf(index) !== -1 ? "selected" : "") + ">" + value + "</option>");
                });

                select.attr('disabled', false);
                select.selectpicker("refresh");
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

function setSaveButton() {
    var currentForm = $("form:visible");
    var saveButton = $("nav.navbar .fa-save");

    if (currentForm.length) {
        saveButton.fadeIn().attr("form", currentForm.attr("id"));
    }
    else {
        saveButton.fadeOut();
    }
}