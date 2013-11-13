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

function RemoveRemote(remoteUrl) {
    $responce = $.ajax({
        type: "DELETE",
        url: remoteUrl,
        async: false
    }).responseText;
    if ($responce != 'OK') {
        alert($responce);
    }
    else {
        window.location.reload();
    }
}

function InitialiseForm() {
    var data = [];
    var theme = getDemoTheme();
    $('#mainSplitter').jqxSplitter({ width: 977, height: 480, theme: theme, panels: [
        { size: 260 },
        { size: 717 }
    ] });

    // Get all sites and add a parent id of 1.
    var remoteSite = getServiceLocation() + "sites";
    var sites = JSON.parse(getRemote(remoteSite));
    for (var s = 0; s < sites.length; s++) {
        var site = sites[s];
        site['treeId'] = site.id;
        site['value'] = "sites/" + site.id + "/finds";
        site['icon'] = "../icons/Colosseum Of Rome.png";
        site['parentId'] = -1;
        data.push(site);
        // Get the trenches for the site.
        var trenches = JSON.parse(getRemote(getServiceLocation() + "sites/" + site.id + "/trenches"));
        for (var t = 0; t < trenches.length; t++) {
            var trench = trenches[t];
            trench['treeId'] = site.id + "." + trench.id;
            trench['value'] = "trenches/" + trench.id + "/finds";
            trench['parentId'] = site.id;
            trench['icon'] = "../icons/Spade.png";
            data.push(trench);
        }
    }
    var localJson = JSON.stringify(data);

    var source = { datatype: "json", datafields: [
        { name: 'icon' },
        { name: 'parentId' },
        { name: 'name' },
        { name: 'treeId' },
        { name: 'value' }
    ], id: 'treeId', localdata: localJson };
    var dataAdapter = new $.jqx.dataAdapter(source);
    dataAdapter.dataBind();
    var records = dataAdapter.getRecordsHierarchy('treeId', 'parentId', 'items', [
        { name: 'name', map: 'label', icon: 'icon', id: 'treeId', value: 'value' }
    ]);
    var treeView = $('#TreeView');
    treeView.jqxTree({ source: records, theme: theme, height: "100%", width: "100%" });
    treeView.on('select', function () {
        var path = getServiceLocation() + "" + treeView.jqxTree('getSelectedItem').value;
        loadDataGrid(path);

    });
    $("#excelExport").click(function () {
        //the selected item in the tree view (tells us what is selected)
        var selected = $('#TreeView').jqxTree('getSelectedItem');
        if (selected != null) {
            var path = selected.value

            // remove to query to get the finds
            path = path.replace('/finds', "");

            path = getServiceLocation() + "export/" + path;
            window.location.href = path;
        }
    });

    $('#Delete').click(function () {
        var selected = $('#TreeView').jqxTree('getSelectedItem');
        if (selected != null) {
            var path = selected.value

            // remove to query to get the finds
            path = path.replace('/finds', "");
            RemoveRemote(getServiceLocation() + "" + path);
        }
    });
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

    $("#DeleteSelected").click(function () {
        var grid = $('#mainGrid');
        var selectedRowIndex = grid.jqxGrid('selectedrowindex');
        if (selectedRowIndex != null) {
            var data = grid.jqxGrid('getrowdata', selectedRowIndex);
            $path = getServiceLocation() + "finds/" + data['uid'];
            RemoveRemote($path);
        }
    });

    $("#EditSelected").click(function () {
        var grid = $('#mainGrid');
        var selectedRowIndex = grid.jqxGrid('selectedrowindex');
        var data = grid.jqxGrid('getrowdata', selectedRowIndex);
        var search = "?id=" + data['uid'];
        window.location = "../Views/AddFind" + search;

    });

    $('#Edit').click(function () {
        var selected = $('#TreeView').jqxTree('getSelectedItem');
        if (selected != null) {
            var path = selected.value

            // remove to query to get the finds
            path = path.replace('/finds', "");
            if (path.indexOf("sites") != -1) {
                path = "../Views/AddSite?id=" + path.replace("sites/", "");
            }
            if (path.indexOf("trenches") != -1) {
                path = "../Views/AddTrench?id=" + path.replace("trenches/", "");
            }
            window.location.href = path;
        }
    });
}
function loadDataGrid(path) {
    var theme = getDemoTheme();
   // var findData = getRemote(path);
    var findSource = {
        datatype: "json",
        datafields: [
            { name: 'siteName', type: 'string' },
            { name: 'trenchName', type: 'string' },
            { name: 'findNumber', type: 'string' },
            { name: 'context', type: 'string' },
            { name: 'numberOfSherds', type: 'string' },
            { name: 'coordinates', type: 'string' },
            { name: 'sherdType', type: 'string' },
            { name: 'fabricType', type: 'string' },
            { name: 'fabricTypeCode', type: 'string' },
            { name: 'wareType', type: 'string' },
            { name: 'baseType', type: 'string' },
            { name: 'rimType', type: 'string' },
            { name: 'fabricColour', type: 'string' },
            { name: 'construction', type: 'string' },
            { name: 'height', type: 'string' },
            { name: 'width', type: 'string' },
            { name: 'thickness', type: 'string' },
            { name: 'weight', type: 'string' },
            { name: 'rimDiameter', type: 'string' },
            { name: 'baseDiameter', type: 'string' },
            { name: 'surfaceTreatment', type: 'string' },
            { name: 'temperType', type: 'string' },
            { name: 'temperQuality', type: 'string' },
            { name: 'manufacture', type: 'string' },
            { name: 'sherdCondition', type: 'string' },
            { name: 'decoration ', type: 'string' },
            { name: 'analysisType', type: 'string' },
            { name: 'sampleNumber', type: 'string' },
            { name: 'minimumNumberOfVessels', type: 'string' },
            { name: 'residues', type: 'string' },
            { name: 'notes',type:'string'}
        ],
        id: 'id',
        url: path
       // localdata: findData
    };
    var findDataAdapter = new $.jqx.dataAdapter(findSource);
    findDataAdapter.dataBind();
    $("#mainGrid").jqxGrid({
        width: "100%",
        height: "100%",
        source: findDataAdapter,
        theme: theme,
        pageable: true,
        pagesize:15,
        sortable: true,
        altrows: true,
        autoheight: true,
        columnsresize: true,
        scrollmode: 'logical',
        columns: [
            { text: 'Site', datafield: 'siteName', width: 100 },
            { text: 'Trench', datafield: 'trenchName', width: 100 },
            { text: 'Find Number', datafield: 'findNumber', width: 100 },
            { text: 'Context', datafield: 'context', width: 100 },
            { text: 'Number of Sherds', datafield: 'numberOfSherds', width: 100 },
            { text: 'GPS Coordinates', datafield: 'coordinates', width: 100 },
            { text: 'Sherd Type', datafield: 'sherdType', width: 100 },
            { text: 'Fabric Type', datafield: 'fabricType', width: 100 },
            { text: 'Fabric Type Code', datafield: 'fabricTypeCode', width: 100 },
            { text: 'Ware Type', datafield: 'wareType', width: 100 },
            { text: 'Base Type', datafield: 'baseType', width: 100 },
            { text: 'Rim Type', datafield: 'rimType', width: 100 },
            { text: 'Fabric Colour', datafield: 'fabricColour', width: 100 },
            { text: 'Construction', datafield: 'construction', width: 100 },
            { text: 'Height', datafield: 'height', width: 100 },
            { text: 'Width', datafield: 'width', width: 100 },
            { text: 'Thickness', datafield: 'thickness', width: 100 },
            { text: 'Weight', datafield: 'weight', width: 100 },
            { text: 'Rim Diameter', datafield: 'rimDiameter', width: 100 },
            { text: 'Base Diameter', datafield: 'baseDiameter', width: 100 },
            { text: 'Surface Treatment', datafield: 'surfaceTreatment', width: 100 },
            { text: 'Temper Type', datafield: 'temperType', width: 100 },
            { text: 'Temper Quality', datafield: 'temperQuality', width: 100 },
            { text: 'Manufacture', datafield: 'manufacture', width: 100 },
            { text: 'Sherd Condition', datafield: 'sherdCondition', width: 100 },
            { text: 'Decoration', datafield: 'decoration ', width: 100 },
            { text: 'Analysis Type', datafield: 'analysisType', width: 100 },
            { text: 'SampleNumber', datafield: 'sampleNumber', width: 100 },
            { text: 'MNV', datafield: 'minimumNumberOfVessels', width: 100 },
            { text: 'Residues', datafield: 'residues', width: 100 },
            {text:'Notes',datafield:'notes',width:100}
        ]
    });

}