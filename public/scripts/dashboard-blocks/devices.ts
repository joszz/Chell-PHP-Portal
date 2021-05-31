import { Block } from "./block.js";
import { Utilities } from "../utilities.js";

export class Devices extends Block {

    public initialize(element: HTMLElement): void {
        super.initialize(element);

        this.initializeEventHandlers();
        this.refresh();

        //this.settings.block.on("click", ".fa-sync", function () {
        //    clearInterval(settings.updateIntervalId);
        //    settings.updateIntervalId = setInterval(function () {
        //        self.checkstates(self);
        //    }, settings.updateInterval);

        //    functions.checkstates(settings.block);
        //});

        //settings.block.on("click", ".btn-danger", function () {
        //    var title = "Wake <span>" + $(this).closest("li").find("div:first").html().trim() + "</span>?";
        //    openConfirmDialog(title, [{ mac: $(this).data("mac") }], function () {
        //        functions.wol($(this));
        //    });
        //});

        //settings.block.on("click", ".btn-success", function () {
        //    var title = "Shutdown <span>" + $(this).closest("li").find("div:first").html().trim() + "</span>?";

        //    openConfirmDialog(title, [{
        //        "shutdown-user": $(this).data("shutdown-user"),
        //        "shutdown-password": $(this).data("shutdown-password"),
        //        "ip": $(this).data("ip")
        //    }], function () {
        //        functions.doShutdown($(this));
        //    });
        //});

        //settings.block.on("click", ".webtemp", function () {
        //    $.getJSON("devices/webtemp/" + $(this).data("deviceid"), function (data) {
        //        $.fancybox.open(data);
        //    });
        //});
    }

    private initializeEventHandlers() {
        this.settings.block.querySelector(".fa-sync").addEventListener("click", () => this.refresh());
        this.settings.block.querySelector(".devicestate").addEventListener("click", (event) => {
            const button = <HTMLElement>event.srcElement;

            if (button.classList.contains("btn-danger")) {

            }
            else if (button.classList.contains("btn-success")) {
                var title = "Shutdown <span>" + $(button).closest("li").find("div:first").html().trim() + "</span>?";

                Utilities.openConfirmDialog(title, [{ mac: button.dataset["mac"] }], () => {
                    this.wol(button);
                });
            }
            //var title = `Wake <span>${btn.srcElement.}`
            //    "Wake <span>" + $(this).closest("li").find("div:first").html().trim() + "</span>?";
        });
    }

    protected refresh(): void {
        this.settings.block.querySelectorAll(".devicestate").forEach((device: HTMLElement) => {
            const ip = device.dataset["ip"];
            const icon = device.querySelector("span.fa");
            const d = new Date();
            const dependentMenuItems = document.querySelectorAll("ul.nav li[data-ip='" + ip + "'");

            icon.classList.remove("fa-power-off");
            icon.classList.add("fa-sync", "fa-spin");

            fetch(`/portal/devices/state/${ip}/${d.getTime()}`).
                then(response => response.json()).
                then(data => {
                    icon.classList.remove("fa-sync", "fa-spin");
                    icon.classList.add("fa-power-off");
                    device.classList.remove("disabled");

                    if (device.dataset["shutdownMethod"] === "none") {
                        device.classList.add("disabled");
                    }

                    if (!data["state"]) {
                        dependentMenuItems.forEach(item => item.classList.add("disabled"));
                        device.parentElement.querySelector(".hypervadmin")?.classList.add("disabled");
                    }
                    else {
                        dependentMenuItems.forEach(item => item.classList.remove("disabled"));
                        device.parentElement.querySelector(".hypervadmin")?.classList.remove("disabled");
                        //todo
                        //dependentMenuItems.forEach(item => item.querySelector("a").removeEventListener("click"));
                    }

                    device.classList.add(`btn-${data["state"] ? "success" : "danger"}`);
                });
        });
    }

    private wol(button: HTMLElement): void {
        $.fancybox.close();

        if (button.id === "confirm-yes") {
            const name = button.parentElement.querySelector("h2 span").textContent.trim();
            const mac = button.parentElement.dataset["mac"];

            fetch(`devices/wol/${mac}`).then(() => {
                Utilities.showAlert("success", "Magic packet send to: " + name);
            });
        }
    }
}