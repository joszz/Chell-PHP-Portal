if (window.self !== window.top) {
    window.top.location = document.getElementById("bootstrap").dataset.baseuri;
}