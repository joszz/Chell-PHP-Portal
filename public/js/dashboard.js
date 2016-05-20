$(function () {
    initializeDashboardEventHandlers();

    $(".sysinfo, #hardware").phpsysinfo().getAll(true);
    $(".processes").phpsysinfo().psstatus(true);
    $(".transmission").transmission().getTorrents(true);
    $(".devices").devices().checkstates();

    $(".movies").gallery();
    $(".episodes").gallery();
    $(".albums").gallery();
});

function initializeDashboardEventHandlers() {
    $("a.toggle").click(function () {
        $(this).toggleClass("glyphicon-minus glyphicon-plus");
        $(this).closest(".panel").find(".list-group, .panel-body").slideToggle("fast");

        $(this).blur();
        return false;
    });

    $("footer a.toggle-all").click(function () {
        var icon = $(this).find("span");

        $("a.glyphicon-" + (icon.hasClass("glyphicon-resize-full") ? "plus" : "minus")).trigger("click");
        icon.toggleClass("glyphicon-resize-full glyphicon-resize-small");
    });
}

function openConfirmDialog(title, data, buttonClick) {
    $("div#confirm-dialog h2").html(title);

    $.each(data, function (index, value) {
        $.each(value, function (index, value) {
            $("div#confirm-dialog").data(index, value);
        });
    });

    $("div#confirm-dialog button").off().on("click", buttonClick);

    $.fancybox({
        content: $("div#confirm-dialog").show(),
        closeBtn: false,
        closeClick: false,
        helpers: {
            overlay: {
                closeClick: false,
                locked: true
            }
        },
        keys: {
            close: null
        }
    });
}