<?php
    require_once 'ErrorResponse.php';
    require_once 'Common.php';
    //$db = new mysqli("localhost", "my_user", "my_password", "world");

    /**
     * Class Site
     */
    class Site
    {

        // The database connection used to query the database.
        private $database;
        private $common;

        /**
         * Short Gets the trenches of the specified site.
         * @param $common Common The common validation to use.
         * @param $db     mysqli the database connection to use.
         * @return Site A new instance of the site object.
         */
        public function __construct($common, $db)
        {
            $this->database = $db;
            $this->common = $common;
        }

        /**
         * Short Gets the trenches of the specified site.
         * @param $id    int The unique identifier of the site.
         * @param $error ErrorResponse The error response which is collating the errors for this query.
         * @return array The trenches which are part of the specified site.
         * An empty array will be returned if the site is unknown.
         */
        public function GetTrenches($id, &$error)
        {
            if (!$this->Exists($id))
            {
                $error->AddNewError("Error", "The site $id does not exist. Unable to get any trenches");

                return Array();
            }
            $trenches = array();
            $trenchesSql = "SELECT * FROM trench WHERE siteId=" . $id;
            $trenchQuery = $this->database->query($trenchesSql);
            if ($trenchQuery)
            {
                while ($TrenchRow = $trenchQuery->fetch_array(MYSQL_ASSOC))
                {
                    array_push($trenches, $TrenchRow);
                }

                return $trenches;
            }
            else
            {
                $error->AddNewError("CRITICAL", $this->database->error);
            }

            return Array();
        }

        /**
         * Short Determine if a site exists.
         * @param $id int the unique id of the site.
         * @return bool true if the site exists.
         */
        public function Exists($id)
        {
            $siteSql = "SELECT * FROM site WHERE id=" . $id;
            $siteQuery = $this->database->query($siteSql);

            return $siteQuery->num_rows > 0;
        }

        /**
         * Short Determine if a site exists using its name.
         * @param $name string the name of the site.
         * @return bool true if the site exists.
         */
        public function NameExists($name)
        {
            $siteSql = "SELECT * FROM site WHERE name='" . $name."'";
            $siteQuery = $this->database->query($siteSql);
            return $siteQuery->num_rows > 0;
        }

        /**
         * Short Gets an array of finds for the given site.
         * @param $id    int The unique identifier of the site.
         * @param $error ErrorResponse The errors for this call.
         * @return array the array of all finds for the given site.
         * if the site id is unknown an empty array is returned.
         */
        public function GetFinds($id, &$error)
        {

            // Get the name of the site
            $siteName = $this->GetName($id, $error);
            $trenchIds = Array();
            $trenchNames = Array();
            $trenchSql = "SELECT * FROM trench WHERE siteId=" . $id;
            $trenchQuery = $this->database->query($trenchSql);
            if ($trenchQuery)
            {
                while ($trenchRow = $trenchQuery->fetch_array(MYSQL_ASSOC))
                {
                    $trenchNames[$trenchRow['id']] = $trenchRow['name'];
                    $trenchIds[$trenchRow['id']] = $trenchRow['id'];
                }
            }
            else
            {
                $error->AddNewError("CRITICAL", $this->database->error);
            }

            $finds = array();
            foreach ($trenchIds as $trenchId)
            {
                $findsSql = "SELECT * FROM find where trenchId=" . $trenchId;
                $findQuery = $this->database->query($findsSql);
                if ($findQuery)
                {
                    while ($findRow = $findQuery->fetch_array(MYSQL_ASSOC))
                    {
                        $newRow = Array();
                        $newRow['siteName'] = $siteName;
                        $newRow['trenchName'] = $trenchNames[$findRow['trenchId']];
                        array_push($finds, array_merge($newRow, $findRow));
                    }
                }
                else
                {
                    $error->AddNewError("CRITICAL", $this->database->error);
                }
            }
            return $finds;
        }

        /**
         * Short Gets the name of the specified site.
         * @param $id int The unique identifier of the site.
         * * @param $error ErrorResponse The error response which is collating the errors for this query.
         * @return string The name of the last site with the given Id.
         * There should only ever be one site as it is a primary key.
         * An error is returned if the trench is unknown.
         */
        public function GetName($id,& $error)
        {
            if (!$this->Exists($id))
            {
                $error->AddNewError("Error", "The specified id ($id) does not exist.");
                return "";
            }
            else
            {
                $trenchNames = $this->GetNamesById($error);
                return $trenchNames[$id];
            }
        }

        /**
         * Short get the mapping of site Id to name.
         * @param $error ErrorResponse The errors if any.
         * @return array the siteIds by site id
         */
        public function GetNamesById(&$error)
        {
            $siteNameMap = Array();
            $siteSql = "SELECT id, name FROM site";
            $siteQuery = $this->database->query($siteSql);
            if ($siteQuery)
            {
                while ($siteRow = $siteQuery->fetch_array(MYSQL_ASSOC))
                {
                    $siteNameMap[$siteRow['id']]= $siteRow['name'];
                }

                return $siteNameMap;
            }
            else
            {
                $error->AddNewError("CRITICAL", $this->database->error);
            }

            return Array();
        }

        /**
         * Short get the mapping of site Id to name.
         * @param $name string the name of the site
         * @param $error ErrorResponse The errors if any.
         * @return array the siteIds by site id
         */
        public function GetIdFromName($name,&$error)
        {
               if(!$this->NameExists($name))
               {
                   $error->AddNewError("Error", "Unknown site (name:$name).");
               }
            else
            {
                $names = $this->GetNamesById($error);
                foreach (array_keys($names) as $key)
                {
                    if (strcasecmp($names[$key],$name)==0)
                    {
                        return $key;
                    }
                }
            }
            return array();
        }

        /**
         * Short Delete the specified site.
         * @param $id    int The unique identifier of the site.
         * @param $error ErrorResponse The errors for this call.
         */
        public function Delete($id,& $error)
        {
            if (!$this->Exists($id, $error))
            {
                $error->AddNewError("Error", "Unable to delete site (id:$id). Site not known");

            }
            if ($this->ContainsTrenches($id))
            {
                $error->AddNewError("Error", "Unable to delete site (id:$id) there are still trenches for this site");
            }
            else
            {
                $sql = 'Delete from site WHERE id=' . $id;
                $deleteQuery = $this->database->query($sql);
                if (!$deleteQuery)
                {
                    $error->AddNewError("CRITICAL", $this->database->error);
                }
            }
        }

        /**
         * Short Delete the specified site.
         * @param $id int The unique identifier of the site.
         * @return boolean true if there are trenches for this site.
         */
        private function ContainsTrenches($id)
        {
            $trenchSql = "SELECT * FROM trench WHERE siteId=" . $id;
            $trenchQuery = $this->database->query($trenchSql);

            return $trenchQuery->num_rows > 0;
        }

        /**
         * Short Insert a Site into te system
         * @param $post       Array The posted data
         * @param $error      ErrorResponse The errors accumulated for this call.
         * @return Array The inserted item.
         */
        public function Insert($post, &$error)
        {
            $data = array();
            foreach ($post as &$value)
            {
                $data = $data + $value;
            }
            unset($value);

            $name = $this->common->Recode($data['name']);
            $this->ValidateSiteName($name, $error);

            $description='';
            if (array_key_exists("description",$data))
            {
                $description = $this->common->Recode($data['description']);
                $this->common->ValidateLength("Description", $description, 200, $error);
            }

            $coordinates='';
            if (array_key_exists("coordinates",$data))
            {
                $coordinates = $this->common->Recode($data['coordinates']);
                $this->common->ValidateLength("Coordinates", $coordinates, 30, $error);
            }

            if ($error->valid)
            {
                $sql = 'INSERT INTO site (name,description,coordinates) VALUES ("' . $name . '","' . $description . '","' . $coordinates . '")';
                $insertQuery = $this->database->query($sql);
                if ($insertQuery != false)
                {
                    return $this->GetById($this->database->insert_id, $error);
                }
                else
                {
                    $error->AddNewError("CRITICAL", $this->database->error);
                }
            }

            return Array();
        }

        /**
         * Short Validate that the name for the site is not already in use
         * @param $name       string the site name.
         * @param $error      ErrorResponse The errors accumulated for this call.
         * @param $exclude    int the id of the site to ignore (Optional)
         * @return bool true if the item is valid.
         */
        function ValidateSiteName($name,& $error, $exclude = -1)
        {
            $valid = true;
            $sites = $this->GetAll($error);
            for ($index = 0; $index < count($sites); $index++)
            {
                if ($sites[$index]['id'] != $exclude)
                {
                    if (strcasecmp($sites[$index]['name'], $name) == 0)
                    {
                        $error->AddNewError("Error", "The site name ($name) is already in use");
                    }
                }
            }

            $this->common->ValidateLength("Name", $name, 50,$error);

            return $error->valid;
        }

        /**
         * Short Gets an array of all site.
         * @param $error ErrorResponse The errors if any.
         * @return array the array of all Sites.
         */
        public function GetAll(&$error)
        {
            $siteSql = "SELECT * FROM site";

            return $this->ProcessSite($siteSql, $error);
        }

        /**
         * Short Gets an array of all site.
         * @param $sql   string The SQL query.
         * @param $error ErrorResponse The errors if any.
         * @return array the array of all sites for the given query.
         * if the site id is unknown an error is returned.
         */
        private function ProcessSite($sql, &$error)
        {
            $sites = array();
            $siteQuery = $this->database->query($sql);
            if ($siteQuery)
            {
                while ($siteRow = $siteQuery->fetch_array(MYSQLI_ASSOC))
                {
                    array_push($sites, $siteRow);
                }

                return $sites;
            }
            else
            {
                $error->AddNewError("CRITICAL", $this->database->error);
            }

            return Array();
        }

        /**
         * Short Get a site by id
         * @param $id    int The unique identifier of the site.
         * @param $error ErrorResponse The errors if any.
         * @return array the array of all sites with the given id.
         * An error is returned if the site is not known.
         */
        public function GetById($id,& $error)
        {
            if (!$this->Exists($id))
            {
                $error->AddNewError("Error", "Unknown site (id:$id).");
            }

            $siteSql = "SELECT * FROM site where id= " . $id;

            return $this->ProcessSite($siteSql, $error);
        }

        /**
         * Short Insert a Site into the system
         * @param $id         int the Unique id of the site.
         * @param $put        Array The put data
         * @param $error      ErrorResponse The errors accumulated for this call.
         * @return Array The inserted item.
         */
        public function Update($id, $put, &$error)
        {
            $data = array();
            foreach ($put as &$value)
            {
                $data = $data + $value;
            }
            unset($value);

            $name = $this->common->Recode($data['name']);
            $description = $this->common->Recode($data['description']);
            $coordinates = $this->common->Recode($data['coordinates']);

            $this->ValidateSiteName($name, $error, $id);
            $this->common->ValidateLength("Description", $description, 200, $error);
            $this->common->ValidateLength("Coordinates", $coordinates, 30, $error);
            if ($error->valid)
            {
                $sql = 'UPDATE site SET name="' . $name . '",description="' . $description . '",coordinates="' . $coordinates . '" WHERE id=' . $id;
                $update = $this->database->query($sql);
                if (!$update == false)
                {
                    return $this->GetById($id, $error);
                }
                else
                {
                    $error->AddNewError("CRITICAL", $this->database->error);
                }
            }

            return Array();
        }
    }

?>