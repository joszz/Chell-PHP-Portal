﻿"use strict";

$(function () {
    var webauth = false;
    $("#webauth").on("click", function () {
        $("#password-group, #rememberme-wrapper").toggle();
        webauth = !webauth;
    });

    $("button[type=submit]").on("click", function () {
        if (webauth) {
            $.ajax({
                type: "POST",
                url: "/portal/session/webauthchallenge",
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
        $("form").attr("action", "/portal/session/webauthauthenticate").submit();
    }
}