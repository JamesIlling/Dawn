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

function updateTrenches(siteId) {
    if (siteId != -1) {
        var theme = getDemoTheme();
        var data = getRemote(getServiceLocation() +"sites/"+ siteId + "/trenches");
        var source = { datatype: "json", datafields: [
            { name: 'name' },
            { name: 'id' }
        ], localdata: data };
        var dataAdapter = new $.jqx.dataAdapter(source);
        dataAdapter.dataBind();

        $('#trenchId').jqxDropDownList({ selectedIndex: 0, source: dataAdapter, displayMember: "name", valueMember: "id", theme: theme });
    }
    else {
        $('#trenchId').jqxDropDownList({ selectedIndex: -1, source: dataAdapter, displayMember: "name", valueMember: "id", theme: theme });
    }
}

function ConfigureDropDowns() {
    var theme = getDemoTheme();
    var data = getRemote(getServiceLocation() + "sites");
    var source = { datatype: "json", datafields: [
        { name: 'name' },
        { name: 'id' }
    ], localdata: data };
    var dataAdapter = new $.jqx.dataAdapter(source);
    dataAdapter.dataBind();
    var sites = $('#sites');
    sites.jqxDropDownList({ selectedIndex: 0, source: dataAdapter, displayMember: "name", valueMember: "id", theme: theme });

    if (sites.jqxDropDownList('getSelectedItem') != null) {
        updateTrenches(sites.jqxDropDownList('getSelectedItem').value);
        sites.on('select', function (event) {
            if (event.args.item != null) {
                updateTrenches(event.args.item.value);
            }
            else {
                updateTrenches(-1);
            }
        });
    }
    else
    {
    updateTrenches(-1);
    }

}

function PopulateForm(parameters) {
    var item = -1;
    var sites = $('#sites')
    if (parameters != null) {

        var siteItems = sites.jqxDropDownList('getItems');
        for (var index = 0; index < siteItems.length; index++) {
            if (siteItems[index].label == parameters['siteName']) {
                item = siteItems[index].index;
            }
        }
    }
    sites.jqxDropDownList('selectIndex', item);
    item = -1;
    if (parameters != null) {
        var trenches = $('#trenchId').jqxDropDownList('getItems');
        if (trenches != null) {
            for (var trenchIndex = 0; trenchIndex < trenches.length; trenchIndex++) {
                if (trenches[trenchIndex].value == parameters['trenchId']) {
                    item = trenches[trenchIndex].index;
                }
            }
        }
    }
    $("#trenchId").jqxDropDownList('selectIndex', item);


    if (parameters != null) {
        if (parameters['id'] != null) {
            // finds
            $('#findNumber').val(parameters['findNumber']);
            $('#context').val(parameters['context']);
            $('#numSherds').val(parameters['numberOfSherds']);
            $('#coordinates').val(parameters['coordinates']);

            $('#sherdType').val(parameters['sherdType']);
            $('#fabricType').val(parameters['fabricType']);
            $('#fabricTypeCode').val(parameters['fabricTypeCode']);
            $('#wareType').val(parameters['wareType']);
            $('#baseType').val(parameters['baseType']);
            $('#rimType').val(parameters['rimType']);
            $('#fabricColour').val(parameters['fabricColour']);
            $('#construction').val(parameters['construction']);

            $('#height').val(parameters['height']);
            $('#width').val(parameters['width']);
            $('#thickness').val(parameters['thickness']);
            $('#Weight').val(parameters['weight']);
            $('#rimDiameter').val(parameters['rimDiameter']);
            $('#baseDiameter').val(parameters['baseDiameter']);

            $('#surfaceTreatment').val(parameters['surfaceTreatment']);
            $('#temperType').val(parameters['temperType']);
            $('#temperQuality').val(parameters['temperQuality']);
            $('#manufacture').val(parameters['manufacture']);
            $('#sherdCondition').val(parameters['sherdCondition']);
            $('#decoration').val(parameters['decoration']);

            $('#scientificAnalysisType').val(parameters['scientificAnalysisType']);
            $('#sampleNumber').val(parameters['sampleNumber']);
            $('#mnvRepresented').val(parameters['mnvRepresented']);
            $('#residues').val(parameters['residues']);
        }
    }
}

function ConfigureButtons() {
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
}

function InitialiseForm() {
    $('#numSherds').spinner({ min: 1, max: 50 });
    ConfigureDropDowns();

    var parameters = getUrlVars();

    $('#id').val(parameters['id']);
    if (parameters['id'] != null) {
        $('.title').html("<h2>Edit find</h2>");
        var remoteUrl = getServiceLocation() + "/finds/" + parameters['id'];
        // Get findData
        var data = JSON.parse($.ajax({
            type: "GET",
            url: remoteUrl,
            async: false
        }).responseText);
        parameters = data[0];
    }
    PopulateForm(parameters);
    ConfigureButtons();
}