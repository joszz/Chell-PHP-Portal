"use strict";

/**
* The Opcache block on the dashboard.
*
* @class Opcache
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

                settings.block.find(".fa-sync").click(function () { functions.update(false); });
                functions.update(true);
            },

            update: function (initialize) {
                initialize = typeof initialize === "undefined" ? false : initialize;
                if (!initialize) {
                    settings.block.isLoading();
                    window.clearInterval(settings.updateIntervalId);
                }

                $.ajax({
                    url: "tdarr",
                    dataType: "json",
                    success: function (data) {
                        functions.initializeChart(data);
                    },
                    complete: function () {
                        settings.updateIntervalId = window.setInterval(functions.update, settings.updateInterval);
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
                const config = {
                    type: "doughnut",
                    data: {
                        labels: Object.keys(chartData),
                        datasets: [{
                            data: Object.values(chartData),
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
                                color: '#FFF'
                            },
                            legend: {
                                position: "top",
                            }
                        }
                    },
                };

                if (settings.chart) {
                    settings.chart.destroy();
                }

                settings.chart= new Chart(settings.block.find("canvas")[0], config);
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);