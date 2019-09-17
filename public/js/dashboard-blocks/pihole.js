"use strict";

/**
* The speedtest block on the dashboard.
* 
* @class Speedtest
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.pihole = function (options) {
        /**
        * All the settings for this block.
        * 
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            url: $(this).data("pihole-url"),
            charts: [
                { name: "Queries today", legendNames: ["Cached", "Not cached", "Blocked"], active: true },
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
                settings.block.on("click", ".fa-sync", function () {
                    functions.refresh();
                });

                functions.refresh();
            },

            refresh: function () {
                $.getJSON(settings.url + "api.php?summaryRaw", function (data) {
                    settings.charts[0].data = [
                        data.queries_cached,
                        data.dns_queries_today - data.queries_cached,
                        data.ads_blocked_today
                    ];

                    functions.initializeChart(settings.charts[0]);
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

                var chart = new Chartist.Pie(".panel.pihole .graph", {
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