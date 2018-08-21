"use strict";

/**
* The speedtest block on the dashboard.
* 
* @class Speedtest
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.speedtest = function (options) {
        /**
        * All the settings for this block.
        * 
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            testOrder: this.data("speedtest-testorder"),
            uploadTime: this.data("speedtest-uploadtime"),
            downloadTime: this.data("speedtest-downloadtime"),
            getISPIP: this.data("speedtest-getispip"),
            distanceUnit: this.data("speedtest-distance"),
            telemetry : this.data("speedtest-telemetry"),
            animationFrameId: -1,
            colors: {
                meterBk: "#E0E0E0",
                dlColor: "#6060AA",
                ulColor: "#309030",
                pingColor: "#AA6060",
                jitColor: "#AA6060",
                progColor: "#EEEEEE"
            }
        }, options);

        var worker = null;
        var data = null;

        /**
        * All the functions for this block.
        * 
        * @property functions
        * @type Object
        */
        var functions = {
            /**
            * Initializes the eventhandlers for the various button clicks.
            * 
            * @method checkstates
            */
            initialize: function () {
                settings.block.find(".fa-play, .fa-stop").on("click", function () {
                    $(this).toggleClass("fa-play fa-stop");
                    functions.startStop();
                });

                //update the UI every frame
                window.requestAnimationFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.msRequestAnimationFrame || (function (callback, element) { setTimeout(callback, 1000 / 60); });

                setTimeout(functions.initUI, 100);
            },

            drawMeter: function (c, amount, bk, fg, progress, prog) {
                var ctx = c.getContext("2d");
                var dp = window.devicePixelRatio || 1;
                var cw = c.clientWidth * dp, ch = c.clientHeight * dp;
                var sizScale = ch * 0.0055;

                if (c.width == cw && c.height == ch) {
                    ctx.clearRect(0, 0, cw, ch);
                }
                else {
                    c.width = cw;
                    c.height = ch;
                }

                ctx.beginPath();
                ctx.strokeStyle = bk;
                ctx.lineWidth = 16 * sizScale;
                ctx.arc(c.width / 2, c.height - 58 * sizScale, Math.abs(c.height / 1.8 - ctx.lineWidth), -Math.PI * 1.1, Math.PI * 0.1);
                ctx.stroke();
                ctx.beginPath();
                ctx.strokeStyle = fg;
                ctx.lineWidth = 16 * sizScale;
                ctx.arc(c.width / 2, c.height - 58 * sizScale, Math.abs(c.height / 1.8 - ctx.lineWidth), -Math.PI * 1.1, amount * Math.PI * 1.2 - Math.PI * 1.1);
                ctx.stroke();

                if (typeof progress !== "undefined") {
                    ctx.fillStyle = prog;
                    ctx.fillRect(c.width * 0.3, c.height - 16 * sizScale, c.width * 0.4 * progress, 4 * sizScale);
                }
            },

            startStop: function () {
                if (worker != null) {
                    //speedtest is running, abort
                    worker.postMessage('abort');
                    worker = null;
                    data = null;

                    cancelAnimationFrame(settings.animationFrameId);

                    functions.initUI();
                }
                else {
                    functions.frame();

                    //test is not running, begin
                    worker = new Worker('js/vendor/speedtest/speedtest_worker.min.js');
                    worker.postMessage('start ' +
                        //Add optional parameters as a JSON object to this command
                        JSON.stringify({
                            url_dl: "../../../speedtest/garbage",
                            url_ul: "../../../speedtest/empty",
                            url_ping: "../../../speedtest/empty",
                            url_getIp: "../../../speedtest/getIP",
                            url_telemetry: "../../../speedtest/telemetry",
                            telemetry_level: settings.telemetry,
                            time_ul: settings.uploadTime,
                            time_dl: settings.downloadTime,
                            getIp_ispInfo: settings.getISPIP,
                            getIp_ispInfo_distance: settings.distanceUnit
                        }));
                    worker.onmessage = function (e) {
                        data = JSON.parse(e.data);

                        if (data.testState >= 4) {
                            //test completed
                            worker = null;
                            functions.updateUI(true);
                            cancelAnimationFrame(settings.animationFrameId);

                            settings.block.find(".fa-play, .fa-stop").removeClass("fa-stop").addClass("fa-play");
                        }
                    };
                }
            },

            mbpsToAmount: function (s) {
                return 1 - (1 / (Math.pow(1.3, Math.sqrt(s))));
            },

            msToAmount: function (s) {
                return 1 - (1 / (Math.pow(1.08, Math.sqrt(s))));
            },

            I: function (id) { return document.getElementById(id); },

            initUI: function () {
                functions.drawMeter(functions.I("dlMeter"), 0, settings.colors.meterBk, settings.colors.dlColor, 0);
                functions.drawMeter(functions.I("ulMeter"), 0, settings.colors.meterBk, settings.colors.ulColor, 0);
                functions.drawMeter(functions.I("pingMeter"), 0, settings.colors.meterBk, settings.colors.pingColor, 0);
                functions.drawMeter(functions.I("jitMeter"), 0, settings.colors.meterBk, settings.colors.jitColor, 0);
                functions.I("dlText").textContent = "";
                functions.I("ulText").textContent = "";
                functions.I("pingText").textContent = "";
                functions.I("jitText").textContent = "";
                functions.I("ip").textContent = "";
            },

            frame: function () {
                settings.animationFrameId = requestAnimationFrame(functions.frame);
                functions.updateUI();
            },

            //this function reads the data sent back by the worker and updates the UI
            updateUI: function (forced) {
                if (worker) {
                    worker.postMessage('status');
                }

                if (data || forced) {
                    var status = data.testState;
                    functions.I("ip").textContent = data.clientIp;
                    functions.I("dlText").textContent = (status == 1 && data.dlStatus == 0) ? "..." : data.dlStatus;
                    functions.drawMeter(functions.I("dlMeter"), functions.mbpsToAmount(Number(data.dlStatus * (status == 1 ? functions.oscillate() : 1))), settings.colors.meterBk, settings.colors.dlColor, Number(data.dlProgress), settings.colors.progColor);
                    functions.I("ulText").textContent = (status == 3 && data.ulStatus == 0) ? "..." : data.ulStatus;
                    functions.drawMeter(functions.I("ulMeter"), functions.mbpsToAmount(Number(data.ulStatus * (status == 3 ? functions.oscillate() : 1))), settings.colors.meterBk, settings.colors.ulColor, Number(data.ulProgress), settings.colors.progColor);
                    functions.I("pingText").textContent = data.pingStatus;
                    functions.drawMeter(functions.I("pingMeter"), functions.msToAmount(Number(data.pingStatus * (status == 2 ? functions.oscillate() : 1))), settings.colors.meterBk, settings.colors.pingColor, Number(data.pingProgress), settings.colors.progColor);
                    functions.I("jitText").textContent = data.jitterStatus;
                    functions.drawMeter(functions.I("jitMeter"), functions.msToAmount(Number(data.jitterStatus * (status == 2 ? functions.oscillate() : 1))), settings.colors.meterBk, settings.colors.jitColor, Number(data.pingProgress), settings.colors.progColor);
                }
            },

            oscillate: function () {
                return 1 + 0.02 * Math.sin(Date.now() / 100);
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);