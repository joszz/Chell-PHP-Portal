(function ($) {
    $.fn.devices = function (options) {
        var settings = $.extend({
            block: this,
            updateInterval: this.data('device-state-interval') * 1000,
            updateIntervalId: -1
        }, options);

        this.find("a.glyphicon-refresh").click(function () {
            clearInterval(settings.updateIntervalId);
            functions.checkstates(settings.block);
            settings.updateIntervalId = setInterval(function () {
                self.checkstates(self);
            }, settings.updateInterval);

            return false;
        });

        this.on("click", "a.btn-danger", function () {
            var title = "Wake <span>" + $(this).closest("li").find("div:first").html().trim() + "</span>?";
            openConfirmDialog(title, [{ mac: $(this).data("mac") }], function () {
                functions.wol($(this));
            });
        });

        this.on("click", "a.btn-success", function () {
            functions.openShutdownDialog($(this));
        });

        $("div#shutdown-dialog button").click(function () {
            return functions.doShutdown();
        });

        var functions = {
            checkstates: function (self) {
                self = typeof self === 'undefined' ? this : self;
                var d = new Date();

                clearInterval(settings.updateIntervalId);
                settings.updateIntervalId = setInterval(function () {
                    self.checkstates(self);
                }, settings.updateInterval);

                settings.block.find("a.devicestate").each(function () {
                    var device = $(this);
                    var icon = $(this).find("span.glyphicon");
                    var ip = $(this).data("ip");
                    var dependentMenuItems = $("ul.nav li[data-ip='" + ip + "'");

                    dependentMenuItems.find("a").bind("click", false);

                    $(this).removeClass("btn-danger btn-success");
                    $(this).addClass("disabled");

                    icon.removeClass("glyphicon-off");
                    icon.addClass("glyphicon-refresh icon-refresh-animate");

                    (function (device, dependentMenuItems, icon, ip) {
                        $.getJSON("devices/state?ip=" + ip + "&" + d.getTime(), "", function (data) {
                            icon.removeClass("glyphicon-refresh icon-refresh-animate");
                            icon.addClass("glyphicon-off");

                            device.removeClass("disabled");

                            if (device.data("shutdown-method") == "none") {
                                device.addClass("disabled");
                            }

                            if (!data["state"]) {
                                dependentMenuItems.addClass("disabled");
                            }
                            else {
                                dependentMenuItems.removeClass("disabled");
                                dependentMenuItems.find("a").unbind("click", false);
                            }

                            device.addClass("btn-" + (data["state"] ? "success" : "danger"));
                        });
                    }(device, dependentMenuItems, icon, ip));
                });
            },

            wol: function (btn) {
                $.fancybox.close();

                if (btn.attr("id") == "confirm-yes") {
                    var name = btn.closest("div").find("h2 span").html().trim();

                    $.get("devices/wol?mac=" + btn.closest("div").data("mac"), function (name) {
                        $("div.alert").addClass("alert-success");
                        $("div.alert").html("Magic packet send to: " + name);
                        $("div.alert").fadeIn("fast");

                        fadeOutAlert();
                    }(name));
                }
            },

            openShutdownDialog: function (btn) {
                btn.blur();
                var name = btn.closest("li").find("div:first").html().trim();

                $("div#shutdown-dialog h2 span").html(name);
                $("div#shutdown-dialog input[name='ip']").val(btn.data("ip"));



                $.fancybox({
                    content: $("div#shutdown-dialog").show(),
                    afterShow: function () {
                        $("div#shutdown-dialog input:first").focus();
                    },
                    helpers: {
                        overlay: {
                            locked: true
                        }
                    }
                });
            },

            doShutdown: function () {
                var user = $("div#shutdown-dialog input[name='user']").val();
                var password = $("div#shutdown-dialog input[name='password']").val();
                var ip = $("div#shutdown-dialog input[name='ip']").val();
                var name = $("div#shutdown-dialog h2 span").html();

                $.get("devices/shutdown?ip=" + ip + "&user=" + user + " &password=" + password, function (data) {
                    $.fancybox.close();

                    if (data == "true") {
                        $("div.alert").addClass("alert-success");
                        $("div.alert").html("Shutdown command send to: " + name);
                    }
                    else {
                        $("div.alert").addClass("alert-danger");
                        $("div.alert").html("Shutdown command failed for: " + name);
                    }

                    $("div.alert").fadeIn("fast");
                    fadeOutAlert();
                });

                return false;
            }
        }

        return functions;
    }
})(jQuery);