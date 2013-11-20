$(document).ready(function ()
{
     var navigationButtons = '<button id="Main" class="navigationButton"><img src="../icons/mainPageIcon.png"/><br/>Main screen</button>'+
                             '<button id="AddFind" class="navigationButton"><img src="../icons/findIcon.png"/><br/>Add Find</button>'+
                             '<button id="AddTrench" class="navigationButton"><img src="../icons/trenchIcon.png"/><br/>Add Trench</button>'+
                             '<button id="AddSite" class="navigationButton"><img src="../icons/siteIcon.png"/><br/>Add site</button>'+
                             '<button id="Settings" class="navigationButton"><img src="../icons/settingsIcon.png"/><br/>Settings</button>'+
                             '<button id="Upload" class="navigationButton"><img src="/icons/uploadIcon.png"/><br/>Upload</button>';

    $('#navigationBar').append(navigationButtons);

    $("#Main").click(function () {
        window.location.href = "../Views/main.html";
    });

    $("#AddFind").click(function () {
        window.location.href = "../Views/addFind.html";
    });
    $("#AddTrench").click(function () {
        window.location.href = "../Views/addTrench.html";
    });
    $("#AddSite").click(function () {
        window.location.href = "../Views/addSite.html";
    });

    $("#Settings").click(function () {
        window.location.href = "../Views/settings.html";
    });

    $("#Upload").click(function () {
        window.location.href = "../Views/upload.html";
    });
});