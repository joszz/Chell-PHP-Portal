(function ($) {
    $.fn.devices = function (options) {
        var settings = $.extend({
            block: this,
            updateInterval: this.data('device-state-interval') * 1000,
            updateIntervalId: -1
        }, options);
        
        return {
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
            }
        }
    }
})(jQuery);