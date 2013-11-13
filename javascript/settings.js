var rootServiceResponse = "Project Dawn Restful service 1.1 - Alpha";

function testService(location) {
    var response = getRemote(location);
    return response == rootServiceResponse;
}

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

    $("#Upload").click(function () {
        window.location = "../Views/Upload.html";
    });

    $("#Settings").click(function () {
        window.location = "../Views/settings.html";
    });

    var serviceLocation = getServiceLocation()

    updateUi(getServiceLocation() == defaultServiceLocation);
    $('#test').click(function () {
        var location = $('#location').val();
        var success = testService(location);
        if (success) {
            alert("Connection OK \n Saving service location");
            setServiceLocation(false, location);
        }
        else {
            alert("Unable to connect to service");

        }

    });
    // $('#location').val(serviceLocation);
    $('#useServerService').click(function () {
        var location;
        if ($('#useServerService').prop('checked') == true) {
            location = defaultServiceLocation;
        }
        else {
            location = $('#location').val();
        }
        updateUi($('#useServerService').prop('checked'));

    });

    $("form[name=frmSettings]").submit(function (eventobj) {

        // prevent the normal submission */
        eventobj.preventDefault();
        var location = $('#location').val();
        setServiceLocation($('#useServerService').prop('checked'), location);
        window.location.href = "../Views/main.html";
        return false;
    });
});


function updateUi(currentLocation) {
    var location = $('#location');
    if (currentLocation) {
        $('#useServerService').prop('checked', true);
        location.attr("disabled", true);
        location.val('');
        $('#test').attr("disabled", true);
    }
    else {
        $('#useServerService').prop('checked', false);
        location.attr("disabled", false);
        location.val(getServiceLocation());
        $('#test').attr("disabled", false);

    }
}
