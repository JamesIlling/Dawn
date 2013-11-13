<?php
include_once('../Data/Common.php');
include_once('../Data/ErrorResponse.php');
include_once('../Data/Site.php');
include_once('../Data/Trench.php');
include_once('../Data/Find.php');

class SiteTest extends PHPUnit_Framework_TestCase
{
    public $db;
    public $common;

    function __construct()
    {
        $this->db= new mysqli("localhost", "root", "", "dawn", 7188);
        $this->common = new Common($this->db);
    }

    protected function setUp()
    {
        $this->ClearDb();
        $this->isDatabaseIsEmpty();
    }
    private function ClearDb()
    {
        // Erase all data in all the tables.
        $query = $this->db->query("DELETE FROM find");
        $query = $this->db->query("DELETE FROM trench");
        $query = $this->db->query("DELETE FROM site");

        // Reset the PK increments.
        $query = $this->db->query("ALTER TABLE find AUTO_INCREMENT =1");
        $query = $this->db->query("ALTER TABLE trench AUTO_INCREMENT =1");
        $query = $this->db->query("ALTER TABLE site AUTO_INCREMENT =1");
    }

    private function isDatabaseIsEmpty()
    {
        $error =new ErrorResponse();
        $db = new mysqli("localhost", "root", "", "dawn", 7188);
        $common = new Common($db);
        $site = new Site($common,$db);
        $this->assertNotNull($site);
        $sites = $site->GetAll($error);

        $this->assertTrue($error->valid);
        $this->assertThat(count($sites),$this->equalTo(0));
    }

    private function InsertSite1($site,$error,$validate = true)
    {
        $sitePost = json_decode('[{"name":"TestSite1"},{"description":"TestDescription1"},{"coordinates":"-1.23,123"}]',true);
        $siteData = $site->Insert($sitePost,$error);
        if ($validate)
        {
        $this->assertTrue($error->valid,'Error inserting site with only a name:'.$error);
            $this->assertEquals($siteData[0]['id'] , 1,'Error unexpected id for site.');
            $this->assertEquals($siteData[0]['name'] , "TestSite1",'Error unexpected name for site.');
            $this->assertEquals($siteData[0]['description'] , "TestDescription1",'Error unexpected description');
            $this->assertEquals($siteData[0]['coordinates'] , "-1.23,123",'Error unexpected coordinates for site.');
        return $siteData;
        }
    }
    private function InsertSite2($site,$error,$validate = true)
    {
        $sitePost = json_decode('[{"name":"TestSite2"},{"description":"TestDescription2"},{"coordinates":"-2.34,234"}]',true);
        $siteData = $site->Insert($sitePost,$error);
        if ($validate)
        {
            $this->assertTrue($error->valid,'Error inserting site with only a name:'.$error);
            $this->assertEquals($siteData[0]['id'] , 2,'Error unexpected id for site.');
            $this->assertEquals($siteData[0]['name'] , "TestSite2",'Error unexpected name for site.');
            $this->assertEquals($siteData[0]['description'] , "TestDescription2",'Error unexpected description');
            $this->assertEquals($siteData[0]['coordinates'] , "-2.34,234",'Error unexpected coordinates for site.');
            return $siteData;
        }
    }
    private function InsertSite3($site,$error,$validate = true)
    {
        $sitePost = json_decode('[{"name":"TestSite3"},{"description":"TestDescription3"},{"coordinates":"-3.45,345"}]',true);
        $siteData = $site->Insert($sitePost,$error);
        if ($validate)
        {
            $this->assertTrue($error->valid,'Error inserting site with only a name:'.$error);
            $this->assertEquals($siteData[0]['id'] , 3,'Error unexpected id for site.');
            $this->assertEquals($siteData[0]['name'] , "TestSite3",'Error unexpected name for site.');
            $this->assertEquals($siteData[0]['description'] , "TestDescription3",'Error unexpected description');
            $this->assertEquals($siteData[0]['coordinates'] , "-3.45,345",'Error unexpected coordinates for site.');
            return $siteData;
        }
    }

    private function AddTrenchToSite1($site,$error,$validate=true)
    {
        $trench=new Trench($this->common,$site,$this->db);
        $postData = json_decode('[{"name":"TestTrench1"},{"sites":"1"},{"description":"TestTrenchDescription1"},{"coordinates":"-0.01,0.01"}]',true);
        $siteData=$site->GetById("1",$error);
        $trenchData= $trench->Insert($postData,$error);
        if ($validate)
        {
            $this->assertTrue($error->valid,'Error inserting Trench');
            $this->assertEquals($siteData[0]['id'] , 1,'Error unexpected id for site.');
            $this->assertEquals($siteData[0]['name'] , "TestSite1",'Error unexpected name for site.');
            $this->assertEquals($siteData[0]['description'] , "TestDescription1",'Error unexpected description');
            $this->assertEquals($siteData[0]['coordinates'] , "-1.23,123",'Error unexpected coordinates for site.');
            $this->assertEquals("1",$trenchData[0]['id']);
            $this->assertEquals("1",$trenchData[0]['siteId']);
            $this->assertEquals("TestTrench1",$trenchData[0]['name']);
            $this->assertEquals("TestTrenchDescription1",$trenchData[0]['description']);
            $this->assertEquals("-0.01,0.01",$trenchData[0]['coordinates']);
            return $trenchData;
        }
    }

    private function AddFindToTrench1($site,$error)
    {
        $trench = new Trench($this->common,$site,$this->db);
        $find = new Find($this->db,$site,$trench,$this->common);
        $postData = json_decode('[{"trenchId":"1"},{"findNumber":"TestFind1"}]',true);
        $findData = $find->Insert($postData,$error);

        $this->assertTrue($error->valid,'Error inserting find');
        $this->assertEquals("1",$findData[0]['id']);
        $this->assertEquals("1",$findData[0]['trenchId']);
        $this->assertEquals("TestFind1",$findData[0]['findNumber']);


    }

    public function testCanNotCreateSiteWithDuplicateName()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        $this->InsertSite1($site,$error);
        $siteData = $this->InsertSite1($site,$error,false);

        $this->assertFalse($error->valid,'Expected error due to duplicate name'.$error);
        $this->assertEquals("The site name (TestSite1) is already in use\r\n",$error->__toString(),"Missing error due to name already existing");
        $this->assertEquals(0,count($siteData),"Missing error due to name already existing");

        $error = new ErrorResponse();
        $sites = $site->GetAll($error);
        $this->assertTrue($error->valid,"Error getting all sites");
        $this->assertEquals(1,count($sites),"Unexpected number of sites");

    }
    public function testCanCreateSiteNameOnly()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();
        $sitePost = json_decode('[{"name":"TestSite2"}]',true);

        $siteData = $site->Insert($sitePost,$error);
        $this->assertTrue($error->valid,'Error inserting site with name and description :'.$error);
        $this->assertEquals($siteData[0]['id'] , 1,'Error unexpected id for site.');
        $this->assertEquals($siteData[0]['name'] , "TestSite2",'Error unexpected name for site.');
        $this->assertEquals($siteData[0]['description'] , "",'Error unexpected description');
        $this->assertEquals($siteData[0]['coordinates'] , "",'Error unexpected coordinates for site.');

    }

    public function testCanCreateSiteNameAndDescription()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();
        $sitePost = json_decode('[{"name":"TestSite2"},{"description":"TestDescription1"}]',true);

        $siteData = $site->Insert($sitePost,$error);
        $this->assertTrue($error->valid,'Error inserting site with name and description :'.$error);
        $this->assertEquals($siteData[0]['id'] , 1,'Error unexpected id for site.');
        $this->assertEquals($siteData[0]['name'] , "TestSite2",'Error unexpected name for site.');
        $this->assertEquals($siteData[0]['description'] , "TestDescription1",'Error unexpected description');
        $this->assertEquals($siteData[0]['coordinates'] , "",'Error unexpected coordinates for site.');

    }

    public function testCanCreateSiteNameDescriptionAndCoordinates()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();
        $sitePost = json_decode('[{"name":"TestSite3"},{"description":"TestDescription1"},{"coordinates":"-1.23,123"}]',true);

        $siteData = $site->Insert($sitePost,$error);
        $this->assertTrue($error->valid,'Error inserting site with name, description and coordinates'.$error);
        $this->assertEquals($siteData[0]['id'] , 1,'Error unexpected id for site.');
        $this->assertEquals($siteData[0]['name'] , "TestSite3",'Error unexpected name for site.');
        $this->assertEquals($siteData[0]['description'] , "TestDescription1",'Error unexpected description');
        $this->assertEquals($siteData[0]['coordinates'] , "-1.23,123",'Error unexpected coordinates for site.');
    }

    public function testGetWithIdGetsSite()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        // Insert a site and validate in inserted correctly
        $this->InsertSite1($site,$error);

        // Get the inserted site via get by id and ensure it is valid
        $siteData = $site->GetById(1,$error);
        $this->assertEquals($siteData[0]['id'] , 1,'Error unexpected id for site.');
        $this->assertEquals($siteData[0]['name'] , "TestSite1",'Error unexpected name for site.');
        $this->assertEquals($siteData[0]['description'] , "TestDescription1",'Error unexpected description');
        $this->assertEquals($siteData[0]['coordinates'] , "-1.23,123",'Error unexpected coordinates for site.');
    }

    public function testGetWithIdReturnsErrorForUnknownSite()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        // Get the inserted site via get by id and ensure it is valid
        $siteData = $site->GetById(1,$error);
        $this->assertFalse($error->valid,"Expected an error as site id:1 does not exist");
        $this->assertEquals(0,count($siteData),"Unexpected number of sites returned.");
        $this->assertEquals("Unknown site (id:1).\r\n",$error->__toString(),"Unexpected value in error text");
    }


    public function testGetAllGetsAllSites()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        // Insert a site and validate in inserted correctly
        $this->InsertSite1($site,$error);
        $this->InsertSite2($site,$error);
        $this->InsertSite3($site,$error);

        $siteData = $site->GetAll($error);
        $this->assertEquals($siteData[0]['id'] , 1,'Error unexpected id for site.');
        $this->assertEquals($siteData[0]['name'] , "TestSite1",'Error unexpected name for site.');
        $this->assertEquals($siteData[0]['description'] , "TestDescription1",'Error unexpected description');
        $this->assertEquals($siteData[0]['coordinates'] , "-1.23,123",'Error unexpected coordinates for site.');
        $this->assertEquals($siteData[1]['id'] , 2,'Error unexpected id for site.');
        $this->assertEquals($siteData[1]['name'] , "TestSite2",'Error unexpected name for site.');
        $this->assertEquals($siteData[1]['description'] , "TestDescription2",'Error unexpected description');
        $this->assertEquals($siteData[1]['coordinates'] , "-2.34,234",'Error unexpected coordinates for site.');
        $this->assertEquals($siteData[2]['id'] , 3,'Error unexpected id for site.');
        $this->assertEquals($siteData[2]['name'] , "TestSite3",'Error unexpected name for site.');
        $this->assertEquals($siteData[2]['description'] , "TestDescription3",'Error unexpected description');
        $this->assertEquals($siteData[2]['coordinates'] , "-3.45,345",'Error unexpected coordinates for site.');
    }
    public function testUpdateSite()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        // Insert a site and validate in inserted correctly
        $this->InsertSite1($site,$error);

        // Update a site and validate in inserted correctly
        $sitePost = json_decode('[{"name":"TestSite2"},{"description":"TestDescription2"},{"coordinates":"-2.34,234"}]',true);
        $siteData = $site->Update("1",$sitePost,$error);
        $this->assertTrue($error->valid,'Error updating site.'.$error);
        $this->assertEquals($siteData[0]['id'] , 1,'Error unexpected id for site.');
        $this->assertEquals($siteData[0]['name'] , "TestSite2",'Error unexpected name for site.');
        $this->assertEquals($siteData[0]['description'] , "TestDescription2",'Error unexpected description');
        $this->assertEquals($siteData[0]['coordinates'] , "-2.34,234",'Error unexpected coordinates for site.');
    }

    public function testUpdateForSiteNonExistentSite()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        // Update a site and validate in inserted correctly
        $sitePost = json_decode('[{"name":"TestSite2"},{"description":"TestDescription2"},{"coordinates":"-2.34,234"}]',true);
        $siteData = $site->Update("2",$sitePost,$error);
        $this->assertFalse($error->valid,'Missing error updating site.'.$error);
        $this->assertEquals("Unknown site (id:2).\r\n",$error->__toString(),"Invalid error");
        $this->assertEquals(0,count($siteData));
    }

    public function testDeleteSite()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        // Insert a site and validate in inserted correctly
        $this->InsertSite1($site,$error);
        $this->InsertSite2($site,$error);

        // delete the site
        $site->Delete("1",$error);
        $this->assertTrue($error->valid,'Error deleting site.'.$error);

        // Get all sites
        $siteData = $site->GetAll($error);
        $this->assertTrue($error->valid,'Error retrieving all sites.'.$error);
        $this->assertEquals(1,count($siteData),"Unexpected number of sites present");

        // Check that only the second site (the one not deleted is present)
        $this->assertTrue($error->valid,'Error inserting site with name, description and coordinates'.$error);
        $this->assertEquals($siteData[0]['id'] , 2,'Error unexpected id for site.');
        $this->assertEquals($siteData[0]['name'] , "TestSite2",'Error unexpected name for site.');
        $this->assertEquals($siteData[0]['description'] , "TestDescription2",'Error unexpected description');
        $this->assertEquals($siteData[0]['coordinates'] , "-2.34,234",'Error unexpected coordinates for site.');
    }

    public function testDeleteForUnknownSite()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        $this->InsertSite1($site,$error);
        $this->InsertSite2($site,$error);

        // delete the site
        $site->Delete("4",$error);
        $this->assertFalse($error->valid,'Missing error when deleting site.'.$error);

        $this->assertEquals("Unable to delete site (id:4). Site not known\r\n",$error->__toString());


        // Get all sites
        $error = new ErrorResponse();
        $siteData = $site->GetAll($error);
        $this->assertTrue($error->valid,'Error retrieving all sites.'.$error);
        $this->assertEquals(2,count($siteData),"Unexpected number of sites present");
        $this->assertEquals($siteData[0]['id'] , 1,'Error unexpected id for site.');
        $this->assertEquals($siteData[0]['name'] , "TestSite1",'Error unexpected name for site.');
        $this->assertEquals($siteData[0]['description'] , "TestDescription1",'Error unexpected description');
        $this->assertEquals($siteData[0]['coordinates'] , "-1.23,123",'Error unexpected coordinates for site.');
        $this->assertEquals($siteData[1]['id'] , 2,'Error unexpected id for site.');
        $this->assertEquals($siteData[1]['name'] , "TestSite2",'Error unexpected name for site.');
        $this->assertEquals($siteData[1]['description'] , "TestDescription2",'Error unexpected description');
        $this->assertEquals($siteData[1]['coordinates'] , "-2.34,234",'Error unexpected coordinates for site.');
    }

    public function testDeleteForSiteWithTrenches()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        $this->InsertSite1($site,$error);
        $this->AddTrenchToSite1($site,$error);

        $site->Delete("1",$error);

        $this->assertFalse($error->valid);
        $this->assertEquals("Unable to delete site (id:1) there are still trenches for this site\r\n",$error->__toString());

        $error=new ErrorResponse();
        $siteData = $site->GetById("1",$error);
        $this->assertEquals(1,count($siteData));
        $this->assertTrue($error->valid,'Error inserting site with only a name:'.$error);
        $this->assertEquals($siteData[0]['id'] , 1,'Error unexpected id for site.');
        $this->assertEquals($siteData[0]['name'] , "TestSite1",'Error unexpected name for site.');
        $this->assertEquals($siteData[0]['description'] , "TestDescription1",'Error unexpected description');
        $this->assertEquals($siteData[0]['coordinates'] , "-1.23,123",'Error unexpected coordinates for site.');

        $trenchData = $site->GetTrenches("1",$error);
        $this->assertEquals("1",$trenchData[0]['id']);
        $this->assertEquals("1",$trenchData[0]['siteId']);
        $this->assertEquals("TestTrench1",$trenchData[0]['name']);
        $this->assertEquals("TestTrenchDescription1",$trenchData[0]['description']);
        $this->assertEquals("-0.01,0.01",$trenchData[0]['coordinates']);

    }

    public function testGetTrenches()
{
    $site = new Site($this->common,$this->db);
    $error = new ErrorResponse();

    $this->InsertSite1($site,$error);
    $this->AddTrenchToSite1($site,$error);

    $error=new ErrorResponse();
    $siteData = $site->GetById("1",$error);
    $this->assertEquals(1,count($siteData));
    $this->assertTrue($error->valid,'Error inserting site with only a name:'.$error);
    $this->assertEquals($siteData[0]['id'] , 1,'Error unexpected id for site.');
    $this->assertEquals($siteData[0]['name'] , "TestSite1",'Error unexpected name for site.');
    $this->assertEquals($siteData[0]['description'] , "TestDescription1",'Error unexpected description');
    $this->assertEquals($siteData[0]['coordinates'] , "-1.23,123",'Error unexpected coordinates for site.');

    $trenchData = $site->GetTrenches("1",$error);
    $this->assertEquals("1",$trenchData[0]['id']);
    $this->assertEquals("1",$trenchData[0]['siteId']);
    $this->assertEquals("TestTrench1",$trenchData[0]['name']);
    $this->assertEquals("TestTrenchDescription1",$trenchData[0]['description']);
    $this->assertEquals("-0.01,0.01",$trenchData[0]['coordinates']);
}
    public function testGetTrenchesForSiteWithNoTrenches()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        $this->InsertSite1($site,$error);

        $trenches = $site->GetTrenches("1",$error);
        $this->assertTrue($error->valid);
        $this->assertEquals(0,count($trenches));
    }

    public function testGetTrenchesForUnknownSite()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        $trenches = $site->GetTrenches("1",$error);
        $this->assertFalse($error->valid);
        $this->assertEquals("The site 1 does not exist. Unable to get any trenches\r\n",$error->__toString());
        $this->assertEquals(0,count($trenches));
    }

    public function testGetFinds()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        $this->InsertSite1($site,$error);
        $this->AddTrenchToSite1($site,$error);
        $this->AddFindToTrench1($site,$error);

        $findData = $site->GetFinds("1",$error);
        $this->assertTrue($error->valid);
        $this->assertEquals("1",count($findData));

        $this->assertEquals("1",$findData[0]['id']);
        $this->assertEquals("1",$findData[0]['trenchId']);
        $this->assertEquals("TestFind1",$findData[0]['findNumber']);

/*
 * $error=new ErrorResponse();
        $siteData = $site->GetById("1",$error);
        $this->assertEquals(1,count($siteData));
        $this->assertTrue($error->valid,'Error inserting site with only a name:'.$error);
        $this->assertEquals($siteData[0]['id'] , 1,'Error unexpected id for site.');
        $this->assertEquals($siteData[0]['name'] , "TestSite1",'Error unexpected name for site.');
        $this->assertEquals($siteData[0]['description'] , "TestDescription1",'Error unexpected description');
        $this->assertEquals($siteData[0]['coordinates'] , "-1.23,123",'Error unexpected coordinates for site.');

        $trenchData = $site->GetTrenches("1",$error);
        $this->assertEquals("1",$trenchData[0]['id']);
        $this->assertEquals("1",$trenchData[0]['siteId']);
        $this->assertEquals("TestTrench1",$trenchData[0]['name']);
        $this->assertEquals("TestTrenchDescription1",$trenchData[0]['description']);
        $this->assertEquals("-0.01,0.01",$trenchData[0]['coordinates']);*/
    }

    public function testGetFindsForSiteWithNoFindsOrTrenches()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        // Insert a site and validate in inserted correctly
        $this->InsertSite1($site,$error);

        $finds = $site->GetFinds("1",$error);
        $this->assertTrue($error->valid,'Error getting finds for site.'.$error);
        $this->assertEquals(0,count($finds));

    }

    public function testGetName()
{$site = new Site($this->common,$this->db);
    $error = new ErrorResponse();

    // Insert a site and validate in inserted correctly
    $this->InsertSite1($site,$error);
    $this->InsertSite2($site,$error);
    $this->InsertSite3($site,$error);

    $siteName = $site->GetName(3,$error);
    $this->assertTrue($error->valid,'Error getting the name of site by id'.$error);
    $this->assertEquals("TestSite3",$siteName,"Incorrect site name returned.");
}
    public function testGetNameForUnknownSite()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();
        $siteName = $site->GetName(3,$error);
        $this->assertFalse($error->valid,'Error missing, getting the name of site by id for non existent site'.$error);
        $this->assertEquals("The specified id (3) does not exist.\r\n",$error->__toString(),"Invalid error message");
        $this->assertEquals("",$siteName,"Incorrect site name returned.");
    }

    public function testGetNamesById()
    {
        $site = new Site($this->common,$this->db);
        $error = new ErrorResponse();

        // Insert a site and validate in inserted correctly
        $this->InsertSite1($site,$error);
        $this->InsertSite2($site,$error);
        $this->InsertSite3($site,$error);

        $names = $site->GetNamesById($error);
        $this->assertTrue($error->valid,'Error getting the names of all sites'.$error);
        $this->assertEquals(3,count($names));
        $this->assertEquals("TestSite1",$names['1']);
        $this->assertEquals("TestSite2",$names['2']);
        $this->assertEquals("TestSite3",$names['3']);

    }
}
