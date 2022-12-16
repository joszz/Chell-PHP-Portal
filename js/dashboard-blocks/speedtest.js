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
            telemetry: this.data("speedtest-telemetry"),
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

                settings.block.find("h4").click(function () {
                    if (settings.block.find(".panel-body").is(":visible")) {
                        functions.initUI();
                    }
                });

                //update the UI every frame
                window.requestAnimationFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.msRequestAnimationFrame || (function (callback, element) { setTimeout(callback, 1000 / 60); });

                setTimeout(functions.initUI, 100);
            },

            /**
             * Called when this plugin is nested in a document with a body#iframe.
             * Initializes Chartist to display statistics.
             *
             * @method initializeIframe
             */
            initializeIframe: function () {
                var panel = settings.block.closest(".panel.with-nav-tabs");

                const data = {
                    labels: settings.block.data("labels").toString().indexOf(",") != -1 ? settings.block.data("labels").split(",") : [settings.block.data("labels")],
                    datasets: [
                        {
                            label: "Download",
                            data: settings.block.data("dl").toString().indexOf(",") != -1 ? settings.block.data("dl").split(",") : [settings.block.data("dl")],
                            borderColor: "#3c763d",
                            backgroundColor: "#d6e9c6",
                        },
                        {
                            label: "Upload",
                            data: settings.block.data("ul").toString().indexOf(",") != -1 ? settings.block.data("ul").split(",") : [settings.block.data("ul")],
                            borderColor: "#337ab7",
                            backgroundColor: "#d9edf7",
                        },
                        {
                            label: "Ping",
                            data: settings.block.data("ping").toString().indexOf(",") != -1 ? settings.block.data("ping").split(",") : [settings.block.data("ping")],
                            borderColor: "#8a6d3b",
                            backgroundColor: "#fcf8e3",
                        },
                        {
                            label: "Jitter",
                            data: settings.block.data("jitter").toString().indexOf(",") != -1 ? settings.block.data("jitter").split(",") : [settings.block.data("jitter")],
                            borderColor: "#a94442",
                            backgroundColor: "#ebccd1",
                        }
                    ]
                };

                const config = {
                    type: "bar",
                    data: data,
                    options: {
                        maintainAspectRatio: false,
                        indexAxis: "y",
                        elements: {
                            bar: {
                                borderWidth: 2
                            }
                        },
                        responsive: true,
                        plugins: {
                            legend: {
                                position: "right"
                            },
                            title: {
                                display: false
                            }
                        }
                    },
                };
                new Chart(settings.block[0], config);

                panel.find(".paginator a:not([href='#'])").click(function () {
                    var href = $(this).attr("href");
                    var start = href.indexOf('stats/');
                    var currentPanel = panel.find(".nav-tabs li.active a").attr("href").replace("#", "") + "/";
                    var page = href.substr(href.lastIndexOf("/") + 1);
                    var newhref = href.substr(0, start + 6) + currentPanel + page;

                    $(this).attr("href", newhref);
                });
            },

            /**
             * Draws the different gauges of the speedtest.
             *
             * @method drawMeter
             */
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

            /**
             * Called when clicking the start/stop button to start the speedtest.
             *
             * @method startStop
             */
            startStop: function () {
                if (worker != null) {
                    //speedtest is running, abort
                    worker.postMessage("abort");
                    worker = null;
                    data = null;

                    cancelAnimationFrame(settings.animationFrameId);

                    functions.initUI();
                }
                else {
                    functions.frame();

                    //test is not running, begin
                    worker = new Worker("dist/js/speedtest_worker.min.js?t=" + Date.now());
                    worker.postMessage("start " +
                        //Add optional parameters as a JSON object to this command
                        JSON.stringify({
                            url_dl: "../../speedtest/garbage",
                            url_ul: "../../speedtest/empty",
                            url_ping: "../../speedtest/empty",
                            url_getIp: "../../speedtest/getIP",
                            url_telemetry: "../../speedtest/telemetry",
                            telemetry_level: settings.telemetry,
                            time_ul_max: settings.uploadTime,
                            time_dl_max: settings.downloadTime,
                            time_auto: false,
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

            /**
             * Calculates the MBps to display in the gauges.
             *
             * @method mbpsToAmount
             */
            mbpsToAmount: function (s) {
                return 1 - (1 / (Math.pow(1.3, Math.sqrt(s))));
            },

            /**
             * Calculates the milliseconds to display in the gauges.
             *
             * @method msToAmount
             */
            msToAmount: function (s) {
                return 1 - (1 / (Math.pow(1.08, Math.sqrt(s))));
            },

            /**
             * Oscillates values so they are less static and more lifelike.
             *
             * @method oscillate
             */
            oscillate: function () {
                return 1 + 0.02 * Math.sin(Date.now() / 100);
            },

            /**
             * First called to draw the blank gauges.
             *
             * @method initUI
             */
            initUI: function () {
                functions.drawMeter($("#dlMeter")[0], 0, settings.colors.meterBk, settings.colors.dlColor, 0);
                functions.drawMeter($("#ulMeter")[0], 0, settings.colors.meterBk, settings.colors.ulColor, 0);
                functions.drawMeter($("#pingMeter")[0], 0, settings.colors.meterBk, settings.colors.pingColor, 0);
                functions.drawMeter($("#jitMeter")[0], 0, settings.colors.meterBk, settings.colors.jitColor, 0);
                $("#dlText").textContent = "";
                $("#ulText").textContent = "";
                $("#pingText").textContent = "";
                $("#jitText").textContent = "";
                $("#ip").textContent = "";
            },

            /**
             * Set for RequestAnimationFrame, updating the UI.
             *
             * @method frame
             */
            frame: function () {
                settings.animationFrameId = requestAnimationFrame(functions.frame);
                functions.updateUI();
            },

            /**
             * this function reads the data sent back by the worker and updates the UI
             *
             * @method updateUI
             */
            updateUI: function (forced) {
                if (worker) {
                    worker.postMessage("status");
                }

                if (data || forced) {
                    var status = data.testState;

                    $("#ip").text(data.clientIp);

                    $("#dlText").text(status == 1 && data.dlStatus == 0 ? "..." : data.dlStatus);
                    functions.drawMeter(
                        $("#dlMeter")[0],
                        functions.mbpsToAmount(Number(data.dlStatus * (status == 1 ? functions.oscillate() : 1))),
                        settings.colors.meterBk,
                        settings.colors.dlColor,
                        Number(data.dlProgress),
                        settings.colors.progColor
                    );

                    $("#ulText").text((status == 3 && data.ulStatus == 0) ? "..." : data.ulStatus);
                    functions.drawMeter(
                        $("#ulMeter")[0],
                        functions.mbpsToAmount(Number(data.ulStatus * (status == 3 ? functions.oscillate() : 1))),
                        settings.colors.meterBk,
                        settings.colors.ulColor,
                        Number(data.ulProgress),
                        settings.colors.progColor
                    );

                    $("#pingText").text(data.pingStatus);
                    functions.drawMeter(
                        $("#pingMeter")[0],
                        functions.msToAmount(Number(data.pingStatus * (status == 2 ? functions.oscillate() : 1))),
                        settings.colors.meterBk,
                        settings.colors.pingColor,
                        Number(data.pingProgress),
                        settings.colors.progColor
                    );

                    $("#jitText").text(data.jitterStatus);
                    functions.drawMeter(
                        $("#jitMeter")[0],
                        functions.msToAmount(Number(data.jitterStatus * (status == 2 ? functions.oscillate() : 1))),
                        settings.colors.meterBk,
                        settings.colors.jitColor,
                        Number(data.pingProgress),
                        settings.colors.progColor
                    );
                }
            }
        };

        //Called as dashboard block
        if (!$("html#iframe").length) {
            functions.initialize();
        }

        return functions;
    };

    //Called from Speedtest stats dialog
    if ($("#speedtest-chart canvas").length) {
        $("#speedtest-chart canvas").speedtest().initializeIframe();
    }
})(jQuery);