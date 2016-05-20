var config;
var rotateMoviesIntervalId, rotateAlbumsIntervalId, rotateEpisodesIntervalId;

$(function () {
    checkDeviceStatesInterval = $(".devices").data("device-state-interval");

    initializeDashboardEventHandlers();

    $(".sysinfo, #hardware").phpsysinfo().getAll(true);
    $(".processes").phpsysinfo().psstatus(true);
    $(".transmission").transmission().getTorrents(true);
    $(".devices").devices().checkstates();

    initGallery("movies", rotateMoviesIntervalId);
    initGallery("episodes", rotateEpisodesIntervalId);
    initGallery("albums", rotateAlbumsIntervalId);
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
        $(this).blur();
    });
}

function initGallery(which, intervalId) {
    intervalId = setInterval(function () {
        rotateGallery(which, "right");
    }, $("div." + which).data("rotate-interval") * 1000);

    $("div." + which + " .glyphicon-chevron-left, div." + which + " .glyphicon-chevron-right").click(function () {
        clearInterval(intervalId);
        rotateGallery(which, $(this).hasClass("glyphicon-chevron-left") ? "left" : "right");

        intervalId = setInterval(function () {
            rotateGallery(which, "right");
        }, $("div." + which).data("rotate-interval") * 1000);

        $(this).blur();
        return false;
    });
}

function rotateGallery(which, direction) {
    var parent = $("div." + which);
    var currentIndex = parent.find("div.item:visible").index();
    var offset = direction == "right" ? 1 : -1;

    nextIndex = parent.find("div.item:eq(" + (currentIndex + offset) + ")").length == 1 ? currentIndex + offset : 0;

    if (currentIndex != nextIndex) {
        parent.find("div.item:eq(" + currentIndex + ")").fadeOut("fast", function () {
            parent.find("div.item:eq(" + nextIndex + ")").fadeIn("fast");
        });
    }
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