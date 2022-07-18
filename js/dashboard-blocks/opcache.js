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

                if (settings.chart) {
                    settings.chart.destroy();
                }

                if (chartData.data.reduce((a, b) => a + b) == 0) {
                    settings.block.find(".no-data").show();
                }
                else {
                    settings.block.find(".no-data").hide();
                    const config = {
                        type: "doughnut",
                        data: {
                            labels: chartData.legendNames,
                            datasets: [{
                                data: chartData.data,
                                backgroundColor: [
                                    "#3c763d",
                                    "rgb(54, 162, 235)",
                                    "rgb(255, 99, 132)",
                                ],
                                hoverOffset: 4
                            }]
                        },
                        plugins: [ChartDataLabels],
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            plugins: {
                                datalabels: {
                                    color: '#FFF'
                                },
                                legend: {
                                    position: "top",
                                },
                                title: {
                                    display: true,
                                    text: chartData.name
                                }
                            }
                        },
                    };

                    settings.chart = new Chart(settings.block.find("canvas")[0], config);
                }
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);