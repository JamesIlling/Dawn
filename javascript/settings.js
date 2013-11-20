var rootServiceResponse = "Project Dawn Restful service 1.1 - Alpha";

function testService(location) {
    var response = getRemote(location);
    return response == rootServiceResponse;
}

$(document).ready(function () {
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


    $('#useServerService').click(function () {
        var location;
        var userServiceLocation = $('#useServerService');
        if (userServiceLocation.prop('checked') == true) {
            location = defaultServiceLocation;
        }
        else {
            location = $('#location').val();
        }
        updateUi(userServiceLocation.prop('checked'));

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
