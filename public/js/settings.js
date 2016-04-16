$(function () {
    //Set focus to correct tab when URL navigated to with location.hash
    if (location.hash) {
        $("a[href='" + location.hash + "']").tab("show");
    }

    $("form#menu a.glyphicon-remove").click(function () {
        return (confirm("Are you sure you want to delete this item?"));
    });
});