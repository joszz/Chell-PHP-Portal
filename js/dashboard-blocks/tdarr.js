"use strict";

/**
* The Tdarr widget.
*
* @class Tdarr
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.tdarr = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            updateInterval: $(this).data("update-interval") * 1000,
            updateIntervalId: -1,
            chart: undefined,
            chartConfig: {
                type: "doughnut",
                data: {
                    labels: undefined,
                    datasets: [{
                        data: undefined,
                        backgroundColor: [
                            "#3c763d",
                            "rgb(54, 162, 235)",
                            "rgb(255, 99, 132)",
                            "#8a6d3b",
                            "#337ab7"

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
                            color: "#FFF",
                            formatter: (value, ctx) => {
                                let sum = 0;
                                let dataArr = ctx.chart.data.datasets[0].data;
                                dataArr.map(data => {
                                    sum += data;
                                });
                                let percentage = (value * 100 / sum).toFixed(2) + "%";
                                return percentage;
                            },
                        },
                        legend: {
                            position: "top",
                        }
                    }
                },
            }
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
                settings.block.find(".fa-rotate").click(function () { functions.update(false); });
                functions.update(true);
            },

            update: function (initialize) {
                initialize = typeof initialize === "undefined" ? false : initialize;
                if (!initialize) {
                    settings.block.isLoading();
                    clearInterval(settings.updateIntervalId);
                }

                $.ajax({
                    url: "tdarr",
                    dataType: "json",
                    success: function (data) {
                        functions.initializeChart(data);
                    },
                    complete: function () {
                        settings.updateIntervalId = setInterval(functions.update, settings.updateInterval);
                        settings.block.isLoading("hide");
                    }
                });
            },

            /**
             * Creates the ChartistJS chart. Called when flipping through the different charts with prev/next buttons.
             *
             * @method initializeChart
             */
            initializeChart: function (chartData) {
                if (settings.chart) {
                    settings.chart.data.datasets.forEach((dataset) => {
                        dataset.data = Object.values(chartData);
                    });
                    settings.chart.update();
                    return;
                }

                settings.chartConfig.data.datasets[0].data = Object.values(chartData);
                settings.chartConfig.data.labels = Object.keys(chartData);
                settings.chart = new Chart(settings.block.find("canvas")[0], settings.chartConfig);
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);