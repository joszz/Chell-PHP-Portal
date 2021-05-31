export class Utilities {
    static openConfirmDialog(title, data, buttonClick) {
        $("div#confirm-dialog h2").html(title);
        $.each(data, function (_index, value) {
            $.each(value, function (index, value) {
                $("div#confirm-dialog").data(index.toString(), value);
            });
        });
        $("div#confirm-dialog button").off().on("click", buttonClick);
        const options = {
            src: "#confirm-dialog",
            opts: {
                modal: true
            }
        };
        $.fancybox.open(options);
    }
}
//# sourceMappingURL=utilities.js.map