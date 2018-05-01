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
        }, options);


        var meterBk = "#E0E0E0";
        var dlColor = "#6060AA",
            ulColor = "#309030",
            pingColor = "#AA6060",
            jitColor = "#AA6060";
        var progColor = "#EEEEEE";
        var w = null; //speedtest worker
        var data = null; //data from worker

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

                setInterval(function () {
                    if (w) w.postMessage('status');
                }, 200);

                //update the UI every frame
                window.requestAnimationFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.msRequestAnimationFrame || (function (callback, element) { setTimeout(callback, 1000 / 60); });

                functions.frame();

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
                ctx.arc(c.width / 2, c.height - 58 * sizScale, c.height / 1.8 - ctx.lineWidth, -Math.PI * 1.1, Math.PI * 0.1);
                ctx.stroke();
                ctx.beginPath();
                ctx.strokeStyle = fg;
                ctx.lineWidth = 16 * sizScale;
                ctx.arc(c.width / 2, c.height - 58 * sizScale, c.height / 1.8 - ctx.lineWidth, -Math.PI * 1.1, amount * Math.PI * 1.2 - Math.PI * 1.1);
                ctx.stroke();

                if (typeof progress !== "undefined") {
                    ctx.fillStyle = prog;
                    ctx.fillRect(c.width * 0.3, c.height - 16 * sizScale, c.width * 0.4 * progress, 4 * sizScale);
                }
            },

            //SPEEDTEST AND UI CODE
            startStop: function () {
                if (w != null) {
                    //speedtest is running, abort
                    w.postMessage('abort');
                    w = null;
                    data = null;
                    functions.initUI();
                }
                else {
                    //test is not running, begin
                    w = new Worker('/portal/js/vendor/speedtest/speedtest_worker.min.js');
                    w.postMessage('start ' + JSON.stringify({
                        url_dl: "../../../speedtest/garbage",
                        url_ul: "../../../speedtest/empty",
                        url_ping: "../../../speedtest/empty",
                        url_getIp: "../../../speedtest/getIP",
                        time_ul: 5,
                        time_dl: 5
                    })); //Add optional parameters as a JSON object to this command
                    w.onmessage = function (e) {
                        data = e.data.split(';');
                        var status = Number(data[0]);
                        if (status >= 4) {
                            //test completed
                            w = null;
                            functions.updateUI(true);
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
                functions.drawMeter(functions.I("dlMeter"), 0, meterBk, dlColor, 0);
                functions.drawMeter(functions.I("ulMeter"), 0, meterBk, ulColor, 0);
                functions.drawMeter(functions.I("pingMeter"), 0, meterBk, pingColor, 0);
                functions.drawMeter(functions.I("jitMeter"), 0, meterBk, jitColor, 0);
                functions.I("dlText").textContent = "";
                functions.I("ulText").textContent = "";
                functions.I("pingText").textContent = "";
                functions.I("jitText").textContent = "";
                functions.I("ip").textContent = "";
            },

            frame: function () {
                requestAnimationFrame(functions.frame);
                functions.updateUI();
            },

            //this function reads the data sent back by the worker and updates the UI
            updateUI: function (forced) {
                if (!forced && (!data || !w)) return;
                var status = Number(data[0]);
                functions.I("ip").textContent = data[4] != "" ? "IP Address: " + data[4] : "";
                functions.I("dlText").textContent = (status == 1 && data[1] == 0) ? "..." : data[1];
                functions.drawMeter(functions.I("dlMeter"), functions.mbpsToAmount(Number(data[1] * (status == 1 ? functions.oscillate() : 1))), meterBk, dlColor, Number(data[6]), progColor);
                functions.I("ulText").textContent = (status == 3 && data[2] == 0) ? "..." : data[2];
                functions.drawMeter(functions.I("ulMeter"), functions.mbpsToAmount(Number(data[2] * (status == 3 ? functions.oscillate() : 1))), meterBk, ulColor, Number(data[7]), progColor);
                functions.I("pingText").textContent = data[3];
                functions.drawMeter(functions.I("pingMeter"), functions.msToAmount(Number(data[3] * (status == 2 ? functions.oscillate() : 1))), meterBk, pingColor, Number(data[8]), progColor);
                functions.I("jitText").textContent = data[5];
                functions.drawMeter(functions.I("jitMeter"), functions.msToAmount(Number(data[5] * (status == 2 ? functions.oscillate() : 1))), meterBk, jitColor, Number(data[8]), progColor);
            },

            oscillate: function () {
                return 1 + 0.02 * Math.sin(Date.now() / 100);
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);