"use strict";

/**
* The HyperVAdmin plugin.
*
* @class HyperVAdmin
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.hypervadmin = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            device_id: this.data("deviceid"),
            vm: {
                stateEnabed: 2,
                stateDisabed: 3,
                toggleStateBaseURL: this.find("#vms").data("togglestate-baseurl") + this.data("deviceid") + "/"
            },
            sites: {
                stateEnabed: 1,
                stateDisabed: 3,
                toggleStateBaseURL: this.find("#sites").data("togglestate-baseurl") + this.data("deviceid") + "/"
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
            * @method checkstates
            */
            initialize: function () {
                if (settings.block.length === 0) {
                    return;
                }

                settings.block.on("click", ".togglestate:visible", function () {
                    var whichTab = $(this).closest(".panel").attr("id");
                    var url = $(this).attr("href");
                    var name = $(this).closest("tr").find("td:eq(1)").text();

                    openConfirmDialog("Are you sure?", [], function () {
                        $.fancybox.close();

                        if ($(this).attr("id") === "confirm-yes") {
                            functions.toggleState(url, name, whichTab);
                        }
                    });

                    return false;
                });

                settings.block.find(".fa-sync").on("click", function () {
                    switch ($(this).closest("a").attr("href")) {
                        case "#vms":
                            functions.refreshVMs(true);
                            break;

                        case "#sites":
                            functions.refreshSites(true);
                            break;
                    }
                });

                settings.block.find(".load .progress-bar").each(function () {
                    $(this).css("width", $(this).data("width"));
                });
            },

            /**
             * Toggles the state of a site or VM/site and refreshes the list when the state toggle was successful.
             *
             * @method toggleState
             * @param {String} url          The URL to call to toggle the state.
             * @param {String} name         The name of the item to toggle the state for.
             * @param {String} whichTab     From which tab the item comes from (either "sites" or "vms").
             */
            toggleState: function (url, name, whichTab) {
                settings.block.isLoading();

                $.ajax({
                    url: url,
                    success: function () {
                        showAlert("success", "State toggled for " + (whichTab === "vm" ? "VM" : "site") + ": " + name);

                        if (whichTab === "vms") {
                            functions.refreshVMs();
                        }
                        else {
                            functions.refreshSites();
                        }
                    }
                });
            },

            /**
             * Refreshes the VM table rows.
             *
             * @method refreshVMs
             * @param {Boolean} showIsLoading   Whether to show the isLoading overlay, defaults to false.
             */
            refreshVMs: function (showIsLoading) {
                showIsLoading = typeof showIsLoading === "undefined" ? false : showIsLoading;

                if (showIsLoading) {
                    settings.block.isLoading();
                }

                $.getJSON("../getVMs/" + settings.device_id, function (data) {
                    var content = settings.block.find("#vms tbody");
                    content.find("tr:not(.hidden)").remove();

                    data.sort(function (a, b) {
                        return a.Name < b.Name ? 1 : -1;
                    });

                    $.each(data, function (_index, item) {
                        var clone = settings.block.find("#vms tr.hidden").clone();
                        var stateToggle = item.State === settings.vm.stateEnabed ? settings.vm.stateDisabed : settings.vm.stateEnabed;

                        clone.find(".name").html(item.Name);
                        clone.find(".load .percent").html(item.CPULoad + "%");
                        clone.find(".load .progress-bar").css("width", item.CPULoad + "%");
                        clone.find(".cores").html(item.CoresAmount);
                        clone.find(".ram").html(item.MemoryTotal + " " + item.MemoryAllocationUnits);
                        clone.find(".mac").html(item.MAC);
                        clone.find(".ontime").html(item.GetOnTimeFormatted);
                        clone.find(".actions a").attr("href", settings.vm.toggleStateBaseURL + item.Name + "/" + stateToggle).addClass("btn-" + (item.State === settings.vm.stateEnabed ? "success" : "danger"));
                        clone.removeClass("hidden");

                        clone.prependTo(content);

                    });
                }).always(function () {
                    settings.block.isLoading("hide");
                });
            },

            /**
             * Refreshes the site table rows.
             *
             * @method refreshSites
             * @param {Boolean} showIsLoading   Whether to show the isLoading overlay, defaults to false.
             */
            refreshSites: function (showIsLoading) {
                showIsLoading = typeof showIsLoading === "undefined" ? false : showIsLoading;

                if (showIsLoading) {
                    settings.block.isLoading();
                }

                $.getJSON("../getSites/" + settings.device_id, function (data) {
                    var content = settings.block.find("#sites tbody");
                    content.find("tr:not(.hidden)").remove();

                    data.sort(function (a, b) {
                        return a.Name < b.Name ? 1 : -1;
                    });

                    $.each(data, function (index, item) {
                        var clone = settings.block.find("#sites tr.hidden").clone();
                        var stateToggle = item.State === settings.sites.stateEnabed ? settings.sites.stateDisabed : settings.sites.stateEnabed;

                        clone.find(".name").html(item.Name);
                        clone.find(".actions a").attr("href", settings.sites.toggleStateBaseURL + item.Name + "/" + stateToggle).addClass("btn-" + (item.State === settings.sites.stateEnabed ? "success" : "danger"));
                        clone.removeClass("hidden");

                        clone.prependTo(content);

                    });
                }).always(function () {
                    settings.block.isLoading("hide");
                });
            }
        };

        functions.initialize();

        return functions;
    };

    if ($(".hypervadmin").length) {
        $(".hypervadmin").hypervadmin();
    }
})(jQuery);