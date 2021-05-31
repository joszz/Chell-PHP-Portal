export class Utilities {
    public static openConfirmDialog(title: string, data: object, buttonClick: () => void) {
        $("div#confirm-dialog h2").html(title);

        $.each(data, function (_index, value) {
            $.each(value, function (index, value: string) {
                $("div#confirm-dialog").data(index.toString(), value);
            });
        });

        $("div#confirm-dialog button").off().on("click", buttonClick);

        const options: FancyBoxGroupItem = {
            src: "#confirm-dialog",
            opts: {
                modal: true
            }
        };
        $.fancybox.open(options);
    }

    public static showAlert(alertType, message) {
        const alertElement = document.querySelector("div.alert");
        alertElement.classList.add("alert-" + alertType);
        alertElement.textContent = message;

        $("div.alert").addClass("alert-" + alertType).html(message).fadeIn("fast");
        this.fadeOutAlert();
    }

    public static fadeOutAlert() {
        window.setTimeout(function () {
            $("div.alert").fadeOut("fast", function () {
                $("div.alert").removeClass("alert-success alert-danger");
            });
        }, 5 * 1000);
    }
}