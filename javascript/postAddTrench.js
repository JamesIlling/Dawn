// over ride the submit of the form so that we can cache the results if off-line or server is not reachable.
$(document).ready(function () {
    $("form[name=frmAddTrench]").submit(function (eventobj) {

        // prevent the normal submission */
        eventobj.preventDefault();
        var data = GetDataFromForm();
        var json = JSON.parse(data);
        var id = json['data'][0]['id'];
            var path;
            var method;
            if (id == '') {
                method = "POST";
                path = getServiceLocation() + "trenches";
            }
            else {
                method = "PUT";
                path = getServiceLocation() + "trenches/" + id;
            }
            $.ajax({ url: path, data: json, type: method })
                .done(function () {
                    window.location.href = "../Views/main.html";
                }).fail(function () {
                    if (arguments[0].status == 400) {
                        alert(arguments[0].responseText);
                    }
                    else {
                        alert("Unable to connect to server");
                    }
                });

        return false;
    });
});

function GetDataFromForm() {
    var data = '{"data":[';
    $(":input:not(:button):not(:submit)").each(function (index) {
        data = data + ('{"' + $(this).closest('[id]').attr('id') + '":"' + encodeURIComponent($(this).val()) + '"},');
    });
    data = data.substr(0, data.length - 1) + ']}';
    return data;
}