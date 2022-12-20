"use strict";

/**
* The CPU widget.
*
* @class CPU
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.cpu = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: $(this),
            loaded: false,
            config: {
                type: "line",
                data: {
                    datasets: [{
                        backgroundColor: "#dff0d8",
                        borderColor: "#3c763d",
                        tension: 0.3,
                        fill: true,
                        data: []
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    elements: {
                        point: {
                            radius: 0
                        }
                    },
                    scales: {
                        x: {
                            type: "realtime",
                            display: false,
                            realtime: {
                                duration: 20000,
                                refresh: 1000,
                                delay: 2000,
                                onRefresh: chart => {
                                    $.ajax({
                                        url: "cpu",
                                        dataType: "json",
                                        success: function (data) {
                                            chart.data.datasets[0].data.push({
                                                x: Date.now(),
                                                y: data
                                            });

                                            if (!settings.loaded) {
                                                setTimeout(function () {
                                                    settings.block.isLoading("hide");
                                                    settings.loaded = true;
                                                }, 2000);
                                            }
                                        }
                                    });
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            min: 0,
                            max: 100,
                            ticks: {
                                callback: function (value) {
                                    return value + "%";
                                },
                            },
                        }
                    }
                }
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
             * Creates a new chart with the given config.
             * @method initialize
             */
            initialize: function () {
                new Chart(settings.block.find("canvas")[0], settings.config);
            }
        };

        functions.initialize();

        return functions;
    };
})(jQuery);