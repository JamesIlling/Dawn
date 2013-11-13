var defaultServiceLocation = "../Data/index.php/";
var serviceLocationKey = "serviceLocation";


function getRemote(remoteUrl) {
    return $.ajax({
        type: "GET",
        url: remoteUrl,
        async: false
    }).responseText;
}

function getServiceLocation() {
    var serviceLocation = window.localStorage.getItem(serviceLocationKey)
    if (serviceLocation == null) {
        setServiceLocation(true, null);
        serviceLocation = defaultServiceLocation;
    }
    return serviceLocation;
}


function setServiceLocation(useDefault, location) {
    if (useDefault) {
        window.localStorage.setItem(serviceLocationKey, defaultServiceLocation);
    }
    else {
        if (!location.endsWith('/')) {
            location = location + '/';
        }

        window.localStorage.setItem(serviceLocationKey, location);
    }
}
