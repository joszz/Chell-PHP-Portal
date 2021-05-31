import { Block } from "./block.js";
import { Utilities } from "../utilities.js";
export class Devices extends Block {
    initialize(element) {
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
    initializeEventHandlers() {
        this.settings.block.querySelector(".fa-sync").addEventListener("click", () => this.refresh());
        this.settings.block.querySelector(".devicestate").addEventListener("click", (event) => {
            const button = event.srcElement;
            if (button.classList.contains("btn-danger")) {
            }
            else if (button.classList.contains("btn-success")) {
                var title = "Shutdown <span>" + $(button).closest("li").find("div:first").html().trim() + "</span>?";
                Utilities.openConfirmDialog(title, [{ mac: button.dataset["mac"] }], () => {
                    this.wol();
                });
            }
            //var title = `Wake <span>${btn.srcElement.}`
            //    "Wake <span>" + $(this).closest("li").find("div:first").html().trim() + "</span>?";
        });
    }
    refresh() {
        this.settings.block.querySelectorAll(".devicestate").forEach((device) => {
            const ip = device.dataset["ip"];
            const icon = device.querySelector("span.fa");
            const d = new Date();
            const dependentMenuItems = document.querySelectorAll("ul.nav li[data-ip='" + ip + "'");
            icon.classList.remove("fa-power-off");
            icon.classList.add("fa-sync", "fa-spin");
            fetch(`/portal/devices/state/${ip}/${d.getTime()}`).
                then(response => response.json()).
                then(data => {
                var _a, _b;
                icon.classList.remove("fa-sync", "fa-spin");
                icon.classList.add("fa-power-off");
                device.classList.remove("disabled");
                if (device.dataset["shutdownMethod"] === "none") {
                    device.classList.add("disabled");
                }
                if (!data["state"]) {
                    dependentMenuItems.forEach(item => item.classList.add("disabled"));
                    (_a = device.parentElement.querySelector(".hypervadmin")) === null || _a === void 0 ? void 0 : _a.classList.add("disabled");
                }
                else {
                    dependentMenuItems.forEach(item => item.classList.remove("disabled"));
                    (_b = device.parentElement.querySelector(".hypervadmin")) === null || _b === void 0 ? void 0 : _b.classList.remove("disabled");
                    //todo
                    //dependentMenuItems.forEach(item => item.querySelector("a").removeEventListener("click"));
                }
                device.classList.add(`btn-${data["state"] ? "success" : "danger"}`);
            });
        });
    }
    wol() {
    }
}
//# sourceMappingURL=devices.js.map