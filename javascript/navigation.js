function showPage(page) {
    if (currentPage != null) {
        $("#page" + currentPage).addClass("hidden");
    }
    $("#page" + page).removeClass("hidden");
    currentPage = page;
    $("#next").attr('disabled', !(currentPage < 6));
    $("#previous").attr('disabled', !(currentPage > 1));
}

var currentPage = 0;

$(document).ready(function () {
    currentPage = 1;
    showPage(currentPage);
    $("#previous").click(function () {
        showPage(currentPage - 1);
    });
    $("#next").click(function () {
        showPage(currentPage + 1);
    });
});


