$(function () {
    $("legend").click(function () {
        var next = $(this).next();
        if (next.hasClass("hidden"))
        {
            next.css("display", "none").removeClass("hidden");
        }
        next.slideToggle();

        $(this).find("i").toggleClass("glyphicon-chevron-down glyphicon-chevron-up");
    });
});