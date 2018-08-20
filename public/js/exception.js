$(function () {
    $("legend").click(function () {
        $(this).next().slideToggle();
        $(this).find("i").toggleClass("glyphicon-chevron-down glyphicon-chevron-up");
    });
});