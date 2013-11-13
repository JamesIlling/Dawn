// This script uses the HTML5 Geolocation API to provide coordinte info,
// It binds the querying of location to the click handler of an object 
// with the id of "geolocation", and will provide the results to the 
// value of an input with an id of "coordinates".

// Attach the Api query to the click handler.
$(document).ready(function () {
    $("#geolocation").click(initiate_geolocation);
});

// Attempt to acquire the location asynchronously.
function initiate_geolocation() {
    navigator.geolocation.getCurrentPosition(handle_geolocation_query, handle_geolocation_error);
}

// Successful query, put coordinate information into the inputs value.
function handle_geolocation_query(position) {
    $("#coordinates").val(position.coords.latitude + ',' + position.coords.longitude);
}

// Unsuccessful query display error message to the user.
function handle_geolocation_error(positionError) {
    alert(positionError.message);
}