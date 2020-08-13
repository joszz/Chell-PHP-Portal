"use strict";

/**
* The speedtest block on the dashboard.
*
* @class Speedtest
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.opcache = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            charts: [
                { name: "Memory", legendNames: ["Used", "Free", "Wasted"], unitIndicator: "MB", active: true },
                { name: "Keys", legendNames: ["Cached", "Free"], unitIndicator: false, active: false },
                { name: "Hits", legendNames: ["Misses", "Hits"], unitIndicator: false, active: false },
                { name: "Restarts", legendNames: ["Memory", "Manual", "Keys"], unitIndicator: false, active: false },
            ],

        }, options);

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
            * @method initialize
            */
            initialize: function () {
                if (settings.block.length === 0) {
                    return;
                }

                $.getJSON("opcache/dataset", function (data) {
                    settings.charts[0].data = data.memory;
                    settings.charts[1].data = data.keys;
                    settings.charts[2].data = data.hits;
                    settings.charts[3].data = data.restarts;

                    functions.initializeChart(settings.charts[0]);
                });

                settings.block.find(".fa-chevron-right, .fa-chevron-left").off().on("click", function () {
                    var offset = 1;

                    var activeChart = settings.charts.find(function (chart) {
                        return chart.active === true;
                    });
                    var activeIndex = settings.charts.indexOf(activeChart);

                    if ($(this).hasClass("fa-chevron-left")) {
                        offset = -1;
                    }

                    var newIndex = activeIndex + offset;

                    if (newIndex >= settings.charts.length) {
                        newIndex = 0;
                    }
                    else if (newIndex < 0) {
                        newIndex = settings.charts.length - 1;
                    }

                    settings.charts[activeIndex].active = false;
                    settings.charts[newIndex].active = true;

                    settings.block.find(".panel-body h4").text(settings.charts[newIndex].name);
                    functions.initializeChart(settings.charts[newIndex]);
                });
            },

            /**
             * Creates the ChartistJS chart. Called when flipping through the different charts with prev/next buttons.
             *
             * @method initializeChart
             */
            initializeChart: function (chartData) {
                if (typeof chartData === "undefined") {
                    chartData = settings.charts[0];
                }

                var chart = new Chartist.Pie(".panel.opcache .graph", {
                    series: chartData.data
                }, {
                    donut: true,
                    donutWidth: 30,
                    showLabel: true,
                    plugins: [
                        Chartist.plugins.legend({
                            position: "bottom",
                            legendNames: chartData.legendNames
                        })
                    ],
                    labelInterpolationFnc: function (value) {
                        return value + (chartData.unitIndicator ? " " + chartData.unitIndicator : "");
                    }
                });

                chart.on("draw", functions.animateChart);
                chart.on("created", function () {
                    setTimeout(function () {
                        settings.block.find(".ct-label ").fadeTo("fast", 1);
                    }, 1000);

                });
            },

            /**
             * Animates the chart, same as the example on ChartistJS website.
             *
             * @method animateChart
             */
            animateChart: function (data) {
                if (data.type === "slice") {
                    var pathLength = data.element._node.getTotalLength();

                    data.element.attr({
                        "stroke-dasharray": pathLength + "px " + pathLength + "px"
                    });

                    var animationDefinition = {
                        "stroke-dashoffset": {
                            id: "anim" + data.index,
                            dur: 750,
                            from: -pathLength + "px",
                            to: "0px",
                            easing: Chartist.Svg.Easing.easeOutQuint,
                            fill: "freeze"
                        }
                    };

                    if (data.index !== 0) {
                        animationDefinition["stroke-dashoffset"].begin = "anim" + (data.index - 1) + ".end";
                    }

                    data.element.attr({
                        "stroke-dashoffset": -pathLength + "px"
                    });

                    data.element.animate(animationDefinition, false);
                }
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);