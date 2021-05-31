export class Block {
    initialize(element) {
        if (element === undefined) {
            return;
        }
        this.settings = {
            block: element,
            updateInterval: parseInt(element.dataset["deviceStateInterval"]) * 1000,
            updateIntervalId: -1
        };
        window.setTimeout(() => this.refresh(), this.settings.updateInterval);
    }
}
//# sourceMappingURL=block.js.map