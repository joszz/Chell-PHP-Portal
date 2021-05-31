/// <reference path ="../../node_modules/@types/jquery/jquery.d.ts"/>
/// <reference path ="../../node_modules/@types/fancybox/index.d.ts"/>

import { Devices } from "./dashboard-blocks/devices.js";

export class Dashboard {

    public init() {
        this.initializeEventHandlers();
        this.initializePlugins();

    }

    private initializePlugins() {
        const devices = new Devices();
        devices.initialize(<HTMLElement>document.querySelector(".devices"));
    }

    private initializeEventHandlers() {
        $(".toggle-collapse, .panel-heading h4").click(function () {
            var panel = $(this).closest(".panel");

            if (panel.find(".toggle-collapse:visible").length !== 0) {
                panel.find(".toggle-collapse").toggleClass("fa-minus fa-plus");
                panel.find(".panel-body:eq(0)").toggleClass("hidden-xs");

                if (panel.hasClass("gallery")) {
                    panel.css("height", panel.find(".panel-body:eq(0)").hasClass("hidden-xs") ? "auto" : "379px");
                }
                //else if (opcache !== false && panel.hasClass("opcache")) {
                //    opcache.initializeChart();
                //}
                //else if (pihole !== false && panel.hasClass("pihole")) {
                //    pihole.initializeChart();
                //}
                //else if (speedtest !== false && panel.hasClass("speedtest")) {
                //    speedtest.initUI();
                //}

                if (!panel.find(".tab-content").length && !panel.find(".panel-body").length) {
                    panel.find(".list-group").toggleClass("hidden-xs");
                }
            }
        });

        $("footer .toggle-all").click(function () {
            $(".fa-" + ($(this).hasClass("fa-expand") ? "plus" : "minus")).trigger("click");
            $(this).toggleClass("fa-expand fa-compress");
        });
    }
}

const dash = new Dashboard();
window.onload = () => dash.init();