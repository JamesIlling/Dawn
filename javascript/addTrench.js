function getRemote(remoteUrl) {
    var response = $.ajax({
        type: "GET",
        url: remoteUrl,
        async: false
    });

    if (response.status == 404) {
        alert("Unable to communicate with service " + getServiceLocation());
        return '[]';
    }
    else {
        return response.responseText;
    }
}

$(document).ready(function () {
    var theme = getDemoTheme();
    var data = getRemote(getServiceLocation() + "sites");
    var source = { datatype: "json", datafields: [
        { name: 'name' },
        { name: 'id' }
    ], localdata: data };

    var dataAdapter = new $.jqx.dataAdapter(source);
    dataAdapter.dataBind();
    $('#sites').jqxDropDownList({ selectedIndex: -1, source: dataAdapter, displayMember: "name", valueMember: "id", theme: theme });

    var $getParameters = getUrlVars();
    if ($getParameters['id'] != null) {
        var remoteUrl = getServiceLocation() + "trenches/" + $getParameters['id'];
        // Get findData
        data = JSON.parse($.ajax({
            type: "GET",
            url: remoteUrl,
            async: false
        }).responseText);
        $getParameters = data[0];

        populateForm($getParameters);
    }
});

function populateForm(parameters) {
    if (parameters != null) {
        $('.title').html("<h2>Edit trench</h2>");
        $('#id').val(parameters['id']);
    }
    var item = -1;
    if (parameters != null) {
        var sites = $('#sites').jqxDropDownList('getItems');
        for (var index = 0; index < sites.length; index++) {
            if (sites[index].value == parameters['siteId']) {
                item = sites[index].index;
            }
        }
    }
    $("#sites").jqxDropDownList('selectIndex', item);
    if (parameters != null) {
        $('#name').val(parameters['name']);
        $('#description').val(parameters['description']);
        $('#coordinates').val(parameters['coordinates']);
    }
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

    $("#Settings").click(function () {
        window.location = "../Views/settings.html";
    });

    $("#Upload").click(function () {
        window.location = "../Views/Upload.html";
    });
});