if (window.self !== window.top) {
    window.top.location = document.getElementById("duojs").dataset.baseuri;
}