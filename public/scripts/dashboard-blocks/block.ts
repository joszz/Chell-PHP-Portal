import { ISettings } from "ISettings";

export abstract class Block {
    protected settings: ISettings;

    protected initialize(element: HTMLElement) {
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

    protected abstract refresh(): void;
}