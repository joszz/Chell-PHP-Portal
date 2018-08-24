if (window.self !== window.top) {
    window.top.location = $("#duo_iframe").data("baseuri");
}