//See media container
function seeMore() {
    var load = document.getElementsByClassName("container-media")[0];
    load.style.display = "flex";
}

// Scroll Down
function scrollDownPosts(y) {
    var height = y;
    var a = window.scrollTo({
        top: height,
        behavior: "smooth"
    });
}

// Scroll Up
function scrollUpPosts(y) {
    var height = y;
    var a = window.scrollTo({
        top: height,
        behavior: "smooth"
    });
}