$(function () {
    //Set focus to correct tab when URL navigated to with location.hash
    if (location.hash) {
        $("a[href='" + location.hash + "']").tab("show");
    }

    $(".nav-tabs a").click(function () {
        location.hash = $(this).attr("href");
    });

    $("form a.glyphicon-remove").click(function () {
        return (confirm("Are you sure you want to delete this item?"));
    });

    $(".actions .add").fancybox({
        maxWidth: "400px"
    });

    $('.nav-tabs').tabCollapse();
});