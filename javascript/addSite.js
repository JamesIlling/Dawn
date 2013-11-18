/**
 * Created by jamesilling on 18/11/2013.
 */
$(document).ready(function () {

    $("#Main").click(function () {
        window.location = "../Views/Main.html";
    });

    $("#AddFind").click(function () {
        window.location = "../Views/AddFind.html";
    });
    $("#AddTrench").click(function () {
        window.location = "../Views/AddTrench.html";
    });
    $("#AddSite").click(function () {
        window.location = "../Views/AddSite.html";
    });

    $("#Settings").click(function () {
        window.location = "../Views/settings.html";
    });
    $("#Upload").click(function () {
        window.location = "../Views/Upload.html";
    });



    var $getParameters = getUrlVars();
    if ($getParameters['id'] != null)
    {
        var remoteUrl = getServiceLocation()+"sites/" + $getParameters['id'];
        // Get findData
        var data = JSON.parse($.ajax({
            type: "GET",
            url: remoteUrl,
            async: false
        }).responseText);
        $getParameters = data[0];
    }
    if ($getParameters != null) {
        $('.title').html("<h2>Edit Site</h2>");
        populateForm($getParameters);
    }
});

function populateForm($parameters) {
    if ($parameters != null) {
        $('#id').val($parameters['id']);
        $('#name').val($parameters['name']);
        $('#description').val($parameters['description']);
        $('#coordinates').val($parameters['coordinates']);
    }
}