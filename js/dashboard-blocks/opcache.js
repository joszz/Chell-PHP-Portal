"use strict";

/**
* The Opcache block on the dashboard.
*
* @class Opcache
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
            chart: undefined

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

                const data = {
                    labels: chartData.legendNames,
                    datasets: [{
                        label: 'My First Dataset',
                        data: chartData.data,
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)'
                        ],
                        hoverOffset: 4
                    }]
                };

                if (settings.chart) {
                    settings.chart.destroy();

                }

                const config = {
                    type: 'doughnut',
                    data: data,
                   
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: chartData.name
                            }
                        }
                    },
                };
                settings.chart = new Chart(document.getElementById("test"), config);
                //var chart = new Chartist.Pie(".panel.opcache .graph", {
                //    series: chartData.data
                //}, {
                //    donut: true,
                //    donutWidth: 30,
                //    showLabel: true,
                //    plugins: [
                //        Chartist.plugins.legend({
                //            position: "bottom",
                //            legendNames: chartData.legendNames
                //        })
                //    ],
                //    labelInterpolationFnc: function (value) {
                //        return value + (chartData.unitIndicator ? " " + chartData.unitIndicator : "");
                //    }
                //});

                //chart.on("draw", functions.animateChart);
                //chart.on("created", function () {
                //    setTimeout(function () {
                //        settings.block.find(".ct-label ").fadeTo("fast", 1);
                //    }, 1000);

                //});
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);