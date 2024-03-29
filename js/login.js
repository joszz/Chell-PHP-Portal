﻿"use strict";

$(function () {
    var webauth = false;
    $("#webauth").on("click", function () {
        $("#password-group, #rememberme-wrapper").toggle();
        webauth = !webauth;
    });

    if ($("#duo_iframe").length) {
        $(window).on("beforeunload", function (_event) {
            $("body").isLoading();
        });

        $("#duo_iframe").on("load", function () {
            $(this).fadeIn();
        });
    }

    $("button[type=submit]").on("click", function () {
        if (webauth) {
            $.ajax({
                type: "POST",
                url: "session/webauthchallenge",
                data: { username: $("#username").val() },
                dataType: "json",
                success: function (data) {
                    webauthnAuthenticate(data, webauthAuthCallback)
                }
            });

            return false;
        }
    });
});

function webauthAuthCallback(success, info) {
    if (success) {
        $("input[name=webauth]").val(info);
        $("form").attr("action", "session/webauthauthenticate").submit();
    }
}