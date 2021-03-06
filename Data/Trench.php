<?php
require_once 'ErrorResponse.php';
require_once 'Site.php';

/**
 * Class Trench
 */
class Trench
{
    private $site;
    private $common;

    /**
     * Short Create a new trench object
     * @param common Common the common validation to use,
     * @param $site  Site The site handler.
     * @return Trench A new instance of the Trench object.
     */
    public function __construct($common, $site)
    {
        $this->common = $common;
        $this->site = $site;
    }

    /**
     * Short Gets the name of the specified trench.
     * @param $id    int The unique identifier of the trench.
     * @param $error ErrorResponse The error response which is collating the errors for this query.
     * @return string The name of the last trench with the given Id.
     * There should only ever be one trench as it is a primary key.
     * An error is returned if the trench is unknown.
     */
    public function GetName($id, $error)
    {
        if (!$this->Exists($id)) {
            $error->AddNewError("Error", "The specified trench (id:$id) does not exist.");
        }

        $trenchNames = $this->GetNamesById($error);
        return $trenchNames[$id];
    }

    /**
     * Short Determine if a trench exists.
     * @param $id int the unique id of the trench.
     * @return bool true if the trench exists.
     */
    public function Exists($id)
    {
        $error = new ErrorResponse();
        $trenchSql = "SELECT * FROM trench WHERE id=:id";
        $trenches = $this->common->ExecuteCommand($trenchSql, array(':id' => $id), $error);

        return count($trenches) > 0;
    }

    /**
     * Short get the mapping of trench Id to name.
     * @param $error ErrorResponse The errors if any.
     * @return array the siteIds by trench id
     */
    public function GetNamesById($error)
    {
        $trenchNameMap = Array();
        $trenchSql = "SELECT id, name FROM trench";
        $trenches = $this->common->ExecuteCommand($trenchSql, array(), $error);
        foreach ($trenches as $trench) {
            $trenchNameMap[$trench['id']] = $trench['name'];
        }
        return $trenchNameMap;
    }

    /**
     * Short Gets an array of all trench.
     * @param $error ErrorResponse The errors if any.
     * @return array the array of all Trenches.
     */
    public function GetAll($error)
    {
        $trenchSql = "SELECT * FROM trench";
        return $this->ProcessTrench($trenchSql, array(), $error);
    }

    /**
     * Short Gets an array of all trench.
     * @param $sql   string The SQL query.
     * @param $data array the named parameters in the sql query
     * @param $error ErrorResponse The errors if any.
     * @return array the array of all trenches for the given query.
     * if the trench id is unknown an error is returned.
     */
    private function ProcessTrench($sql, $data, $error)
    {
        $trenches = $this->common->ExecuteCommand($sql, $data, $error);
        for ($i = 0; $i < count($trenches); $i++) {
            $trenches[$i]['siteName'] = $this->site->GetName($trenches[$i]['siteId'], $error);
        }
        return $trenches;
    }

    /**
     * Short get the mapping of trenches to sites.
     * @param $error ErrorResponse The errors if any.
     * @return array the siteIds by trench id
     */
    public function GetTrenchSiteMap($error)
    {
        $trenchSiteMap = Array();
        $trenchSql = "SELECT id, siteId FROM trench";
        $trenches = $this->common->ExecuteCommand($trenchSql, array(), $error);
        foreach ($trenches as $trench) {
            $trenchSiteMap[$trench['id']] = $trench['siteId'];
        }
        return $trenchSiteMap;
    }

    /**
     * Short Attempt to delete a trench
     * @param $id      int The Id of the trench to look at.
     * @param $error   ErrorResponse The compiled errors for this call.
     */
    public function Delete($id, $error)
    {

        if (!$this->Exists($id, $error)) {
            $error->AddNewError("Error", "Unable to delete trench (id:$id). The trench is not known.");
        }

        if (count($this->GetFinds($id, $error)) > 0) {
            $error->AddNewError("Error", "Unable to delete trench (id:$id), there are finds for this trench");
        }

        if ($error->valid) {
            $sql = 'DELETE FROM trench WHERE id=:id';
            $this->common->ExecuteCommand($sql, array(':id' => $id), $error);
        }
    }

    /**
     * Short Gets the name of the specified trench.
     * @param $id    int The unique identifier of the trench.
     * @param $error ErrorResponse The error response which is collating the errors for this query.
     * @return array The list of all the finds for this trench.
     */
    public function GetFinds($id, $error)
    {
        $trench = $this->GetById($id, $error);
        $trench = $trench[0];
        $siteName = $this->site->GetName($trench['siteId'], $error);
        $finds = array();

        if ($error->valid) {
            $findsSql = "SELECT * FROM find WHERE trenchId=:id";
            $finds = $this->common->ExecuteCommand($findsSql, array(':id' => $trench['id']), $error);
            for ($i = 0; $i < count($finds); $i++) {
                $finds[$i]['siteName'] = $siteName;
                $finds[$i]['trenchName'] = $trench['name'];
            }
        }
        return $finds;
    }

    /**
     * Short Get a trench by id
     * @param $id    int The unique identifier of the trench.
     * @param $error ErrorResponse The errors if any.
     * @return array the array of all trenches with the given id.
     * An error is returned if the trench is not known.
     */
    public function GetById($id, $error)
    {
        if (!$this->Exists($id)) {
            $error->AddNewError("Error", "Unknown trench (id:$id).");
        }

        $trenchSql = "SELECT * FROM trench WHERE id=:id";
        return $this->ProcessTrench($trenchSql, array(':id' => $id), $error);
    }

    /**
     * Short Validate The name of the trench is not in use. Error messages are put straight to the output
     * @param $id      int The Id of the trench to look at.
     * @param $error   ErrorResponse The compiled errors for this call.
     * @param $put     Array The parameters passed in the put command.
     * @return Array The updated trench if any.
     */
    public function Update($id, $put, $error)
    {
        $data = array();

        foreach ($put as &$value) {
            $data = $data + $value;
        }
        unset($value);

        $name = urldecode($data["name"]);
        $site = urldecode($data["sites"]);
        $description = urldecode($data['description']);
        $coordinates = urldecode($data['coordinates']);

        if (!$this->site->Exists($site)) {
            $error->AddNewError("Error", "Unable to add new Trench the Site is unknown");
        }

        $this->ValidateTrenchName($name, $site, $error, $id);

        $this->common->ValidateLength("Description", $description, 200, $error);
        $this->common->ValidateLength("Coordinates", $coordinates, 30, $error);

        if ($error->valid) {

            $sql = 'UPDATE trench SET name=:name, description=:description, coordinates=:coordinates, siteId=:siteId WHERE id=:id';
            $data = array(':name' => $name, ':description' => $description, ':coordinates' => $coordinates, ':siteId' => $site, ':id' => $id);
            $this->common->ExecuteCommand($sql, $data, $error);
            return $this->GetById($id, $error);
        }

        return Array();
    }

    /**
     * Short Validate The name of the trench is not in use. Error messages are put straight to the output
     * @param $name    string The name of the trench.
     * @param $siteId  int The Id of the site to look at.
     * @param $error   ErrorResponse The compiled errors for this call.
     * @param $exclude int The Id of the site if it should be ignored.
     * @return bool true if the item is valid.
     */
    private function ValidateTrenchName($name, $siteId, $error, $exclude = -1)
    {
        $trenches = $this->site->GetTrenches($siteId, $error);
        for ($index = 0; $index < count($trenches); $index++) {
            if ($index != $exclude) {
                if (strcasecmp($trenches[$index]['name'], $name) == 0) {
                    $error->AddNewError("Error", "Name:$name is already in use");
                }
            }
        }

        $this->common->ValidateLength("Name", $name, 20, $error);
    }

    /**
     * Short Insert a new trench into the system
     * @param $post    Array The post data to use to create the trench
     * @param $error   ErrorResponse The compiled errors for this call.
     * @return Array The inserted trench.
     */
    public function Insert($post, $error)
    {
        {
            $data = array();

            foreach ($post as &$value) {
                $data = $data + $value;
            }
            unset($value);

            $name = urldecode($data["name"]);
            $site = urldecode($data["sites"]);
            $description = urldecode($data['description']);
            $coordinates = urldecode($data['coordinates']);

            $this->ValidateTrenchName($name, $site, $error);

            if (!$this->site->Exists($site)) {
                $error->AddNewError("Error", "Unable to add new Trench the Site is unknown");
            }

            $this->common->ValidateLength("Description", $description, 200, $error);
            $this->common->ValidateLength("Coordinates", $coordinates, 30, $error);

            if ($error->valid) {

                $sql = 'INSERT INTO trench (siteId,name,description,coordinates) VALUES (:site,:name,:description,:coordinates)';
                $data = array(':site' => $site, ':name' => $name, ':description' => $description, ':coordinates' => $coordinates);
                $this->common->ExecuteCommand($sql, $data, $error);
                return $this->GetFromName($this->site->GetName($site, $error), $name, $error);
            }
        }
        return Array();
    }

    /**
     * Short Insert a new trench into the system
     * @param $trenchName    string The name of the trench
     * @param $siteName string the name of the site
     * @param $error   ErrorResponse The compiled errors for this call.
     * @return bool indicating if the site exists.
     */
    public function NameExists($trenchName, $siteName, $error)
    {
        $namesById = $this->GetNamesById($error);
        foreach (array_keys($namesById) as $id) {
            if (strcasecmp($namesById[$id], $trenchName) == 0) {
                $trench = $this->GetById($id, $error);
                if (count($trench) > 0) {
                    if (strcasecmp($trench[0]['siteName'], $siteName) == 0) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Short Look up a trench by site name and trench name
     * @param $siteName string The name of the site.
     * @param $trenchName string The name of the trench.
     * @param $error   ErrorResponse The compiled errors for this call.
     * @return array the trench
     */
    public function GetFromName($siteName, $trenchName, $error)
    {
        $namesById = $this->GetNamesById($error);
        foreach (array_keys($namesById) as $id) {
            if (strcasecmp($namesById[$id], $trenchName) == 0) {
                $trench = $this->GetById($id, $error);
                if (strcasecmp($trench[0]['siteName'], $siteName) == 0) {
                    return $trench;
                }
            }
        }
        $error->AddNewError("Error", "Unknown trench");
        return array();
    }
}

?>