<?php
require_once 'Common.php';
require_once 'Site.php';
require_once 'Trench.php';
require_once 'Export.php';
require_once 'Find.php';
require_once 'Import.php';

require_once 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array('debug' => true));


if (getenv('S2G_SERVER_SOFTWARE') !=null)
{
    // we are running on Server2Go use its DB.
    $db=new PDO("mysql:dbname=dawn;host=127.0.0.1;port=7188","root","");
}
else
{
    // Using MAMPS db on the default port.
    $db=new PDO("mysql:dbname=dawn;host=127.0.0.1;port=3306","root","root");
}
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$common = new Common($db);
$site = new Site($common);
$trench = new Trench($common, $site);
$find = new Find( $site, $trench, $common);
$export = new Export($site, $trench);

//===========================================================================
// Display version.
//===========================================================================
$app->get("/", function () {
    echo "Project Dawn Restful service 1.1 - Alpha";
});

//===========================================================================
// Display the help file (help.html)
//===========================================================================
$app->get("/help", function () {
    readFile("help.html");
});

//===========================================================================
// Get all the sites
//===========================================================================
$app->get("/sites", function () use ($app, $site) {

    $error = new ErrorResponse();
    $sites = $site->GetAll($error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($sites);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Get a site by Id
//===========================================================================
$app->get("/sites/:id", function ($id) use ($app, $site) {
    $error = new ErrorResponse();
    $sites = $site->GetById($id, $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($sites);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Get trenches for a site by Id
//===========================================================================
$app->get("/sites/:id/trenches", function ($id) use ($app, $site) {
    $error = new ErrorResponse();
    $trenches = $site->GetTrenches($id, $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($trenches);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Get finds for a site by Id
//===========================================================================
$app->get("/sites/:id/finds", function ($id) use ($app, $site) {
    $error = new ErrorResponse();
    $finds = $site->GetFinds($id, $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");

        echo json_encode($finds);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});
//===========================================================================
// Insert a site
//===========================================================================
$app->post("/sites", function () use ($app, $site) {
    $error = new ErrorResponse();
    $sites = $site->Insert($app->request()->post('data'), $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($sites);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Update a site by Id
//===========================================================================
$app->put("/sites/:id", function ($id) use ($app, $site) {
    $error = new ErrorResponse();
    $sites = $site->Update($id, $app->request()->put('data'), $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($sites);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Delete a site by Id
//===========================================================================
$app->delete("/sites/:id", function ($id) use ($app, $site) {
    $error = new ErrorResponse();
    $site->Delete($id, $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "text/plain");
        echo "OK";
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Get all trenches
//===========================================================================
$app->get("/trenches", function () use ($app, $trench) {

    $error = new ErrorResponse();
    $trenches = $trench->GetAll($error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($trenches);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Get a trench by Id
//===========================================================================
$app->get("/trenches/:id", function ($id) use ($app, $trench) {
    $error = new ErrorResponse();
    $trenches = $trench->GetById($id, $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($trenches);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Get Finds for a trench by Id
//===========================================================================
$app->get("/trenches/:id/finds", function ($id) use ($app, $trench, $site) {
    $error = new ErrorResponse();
    $finds = $trench->GetFinds($id, $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($finds);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Insert a trench
//===========================================================================
$app->post("/trenches", function () use ($app, $trench) {
    $error = new ErrorResponse();
    $trench = $trench->Insert($app->request()->post('data'), $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($trench);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Update a trench by Id
//===========================================================================
$app->put("/trenches/:id", function ($id) use ($app, $trench) {
    $error = new ErrorResponse();
    $trench = $trench->Update($id, $app->request()->put('data'), $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($trench);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Delete a trench by Id
//===========================================================================
$app->delete("/trenches/:id", function ($id) use ($app, $trench) {
    $error = new ErrorResponse();
    $trench->Delete($id, $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "text/plain");
        echo "OK";
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Get All Finds
//===========================================================================
$app->get("/finds", function () use ($app, $find) {
    $error = new ErrorResponse();
    $finds = $find->GetAll($error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($finds);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Get a find by Id
//===========================================================================
$app->get("/finds/:id", function ($id) use ($app, $find) {
    $error = new ErrorResponse();
    $finds = $find->GetById($id, $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($finds);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Insert a find
//===========================================================================
$app->post("/finds", function () use ($app, $find) {
    $error = new ErrorResponse();
    $finds = $find->Insert($app->request()->post('data'), $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($finds);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Update A find
//===========================================================================
$app->put("/finds/:id", function ($id) use ($app, $find) {
    $error = new ErrorResponse();
    $finds = $find->Update($id, $app->request()->put('data'), $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($finds);
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Delete a find by Id
//===========================================================================
$app->delete("/finds/:id", function ($id) use ($app, $find) {
    $error = new ErrorResponse();
    $find->Delete($id, $error);
    if ($error->valid) {
        $app->response()->setStatus(200);
        $app->response()->header("Content-Type", "text/plain");
        echo "OK";
    } else {
        $app->response()->setStatus(400);
        $app->response()->header("Content-Type", "text/plain");
        echo $error;
    }
});

//===========================================================================
// Export all data for a site.
//===========================================================================
$app->get("/export/trenches/:id", function ($id) use ($app, $trench, $export) {
    $error = new ErrorResponse();
    $trenchName = $trench->GetName($id, $error);
    $excel = $export->Trench($id, $error);
    if ($error->valid) {
        $header = $app->response();
        $header['Content-Type'] = "application/vnd.ms-excel";
        $header['Content-Disposition'] = 'attachment;filename="' . $trenchName . '.xls"';
        $header['Cache-Control'] = 'max-age=0';
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save("php://output");
    } else {
        $app->response()->header("Content-Type", "text/plain");
        $app->response()->setStatus(400);
        echo $error;
    }
});

//===========================================================================
// Export all data for a site.
//===========================================================================
$app->get("/export/sites/:id", function ($id) use ($app, $site, $export) {
    $error = new ErrorResponse();

    $siteName = $site->GetName($id, $error);
    $excel = $export->Site($id, $error);
    if ($error->valid) {
        $header = $app->response();
        $header['Content-Type'] = "application/vnd.ms-excel";
        $header['Content-Disposition'] = 'attachment;filename="' . $siteName . '.xls"';
        $header['Cache-Control'] = 'max-age=0';

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save("php://output");
    } else {
        $app->response()->header("Content-Type", "text/plain");
        $app->response()->setStatus(400);
        echo $error;
    }
});

$app->post("/import", function () use ($app, $site, $trench, $find, $export) {
    $error = new ErrorResponse();
    if ($_FILES["file"]["error"] > 0) {
        $error->AddNewError("Error", $_FILES["file"]["error"]);
    }
    else {

        $import = new Import($site, $trench, $find);
        $import->Import($_FILES["file"]["tmp_name"], $error);
    }

    if (!$error->valid) {
        $app->response()->header("Content-Type", "text/plain");
        $app->response()->setStatus(400);
    } else {
        $app->response()->header("Content-Type", "text/plain");
        $app->response()->setStatus(200);
    }
});


$app->run();
?>