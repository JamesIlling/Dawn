<?php
include_once('../Data/Common.php');
include_once('../Data/ErrorResponse.php');
include_once('../Data/Site.php');
include_once('../Data/Trench.php');
include_once('../Data/Find.php');

class PreDeploymentTests extends PHPUnit_Framework_TestCase
{
    public $db;
    public $common;
    public $site;
    public $trench;
    public $find;
    public $export;

    function __construct()
    {
        $this->db= new mysqli("localhost", "root", "", "dawn", 7188);
        $this->common = new Common($this->db);
        $this->site = new Site($this->common,$this->db);
        $this->trench =new Trench($this->common,$this->site,$this->db);
        $this->find=new Find($this->db,$this->site,$this->trench,$this->common);
    }

private function ClearDatabase()
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

    private function GetNextAutoIncrementId($tableName)
    {
        $result = $this->db->query("SHOW TABLE STATUS LIKE '$tableName'");
        $row = $result->fetch_array(MYSQL_ASSOC);
        $nextId = $row['Auto_increment'];
        return $nextId;
    }

    public function testNumberOfSites()
    {
        $error = new ErrorResponse();
        $sites = $this->site->GetAll($error);
        $this->assertEquals(0,count($sites),"Unexpected number of sites");
        $this->assertEquals(1,$this->GetNextAutoIncrementId("site"),"Unexpected next id");
    }

    public function testNumberOfTrenches()
    {
        $error = new ErrorResponse();
        $trenches = $this->trench->GetAll($error);
        $this->assertEquals(0,count($trenches),"Unexpected number of trenches");
        $this->assertEquals(1,$this->GetNextAutoIncrementId("trench"),"Unexpected next id");
    }

    public function testNumberOfFinds()
    {
        $error = new ErrorResponse();
        $finds = $this->find->GetAll($error);
        $this->assertEquals(0,count($finds),"Unexpected number of trenches");
        $this->assertEquals(1,$this->GetNextAutoIncrementId("find"),"Unexpected next id");
    }
}