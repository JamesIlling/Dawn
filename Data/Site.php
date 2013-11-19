<?php
    require_once 'ErrorResponse.php';
    require_once 'Common.php';


    /**
     * Class SiteHandler
     */
    class Site
    {
        private $common;

        /**
         * Short Gets the trenches of the specified site.
         * @param $common Common The common validation to use.
         * @return Site A new instance of the site object.
         */
        public function __construct($common)
        {
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

            $trenchesSql = 'SELECT * FROM trench WHERE siteId=:id';
            $trenches = $this->common->ExecuteCommand($trenchesSql,array(':id'=>$id),$error);

            return $trenches;
        }

        /**
         * Short Determine if a site exists.
         * @param $id int the unique id of the site.
         * @return bool true if the site exists.
         */
        public function Exists($id)
        {
            $error = new ErrorResponse();
            $siteSql = 'SELECT COUNT(*) FROM site WHERE id=:id';
            $sites = $this->common->ExecuteCommand($siteSql,array(':id'=>$id),$error);
            return $sites > 0;
        }

        /**
         * Short Determine if a site exists using its name.
         * @param $name string the name of the site.
         * @return bool true if the site exists.
         */
        public function NameExists($name)
        {
            $error = new ErrorResponse();
            $siteSql = 'SELECT * FROM site WHERE name=:name';
            $sites = $this->common->ExecuteCommand($siteSql,array(':name'=>$name),$error);
            return count($sites) > 0;
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
            $finds = array();
            // Get the name of the site
            $siteName = $this->GetName($id, $error);
            foreach ($this->GetTrenches($id,$error) as $trench )
            {
                if (count($trench)>0)
                {
                $findsSql = "SELECT * FROM find where trenchId= :trenchId";
                $findsInDb = $this->common->ExecuteCommand($findsSql,array(':trenchId'=>$trench['id']),$error);
                for ($i=0;$i<count($findsInDb);$i++)
                {

                    $findsInDb[$i]['siteName'] = $siteName;
                    $findsInDb[$i]['trenchName'] = $trench['name'];
                }
                $finds = array_merge($finds,$findsInDb);
            }
            }
            return $finds;
        }

        /**
         * Short Gets the name of the specified site.
         * @param $id int The unique identifier of the site.
         * @param $error ErrorResponse The error response which is collating the errors for this query.
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
         * @return array the siteNames by site id
         */
        public function GetNamesById(&$error)
        {
            $siteNameMap = Array();
            $siteSql = "SELECT * FROM site";
            $siteInfo = $this->common->ExecuteCommand($siteSql,null,$error);

            foreach ($siteInfo as $site)
            {
                $siteNameMap[$site['id']]= $site['name'];
            }

            return $siteNameMap;
        }

        /**
         * Short get the mapping of site Id to name.
         * @param $name string the name of the site
         * @param $error ErrorResponse The errors if any.
         * @return int the siteIds by site id
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
                        return (int)$key;
                    }
                }
            }
            return 0;
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
                $sql = 'Delete from site WHERE id=:id';
                $this->common->ExecuteCommand($sql,array(':id'=>$id),$error);
            }
        }

        /**
         * Short Delete the specified site.
         * @param $id int The unique identifier of the site.
         * @return boolean true if there are trenches for this site.
         */
        private function ContainsTrenches($id)
        {
            $error =new ErrorResponse();
            $trenchSql = "SELECT * FROM trench WHERE siteId=:id";
            $trenches = $this->common->ExecuteCommand($trenchSql,array(':id'=> $id),$error);
            return count($trenches) > 0;
        }

        /**
         * Short Insert a Site into te system
         * @param $post       Array The posted data
         * @param $error      ErrorResponse The errors accumulated for this call.
         * @return Site The inserted item.
         */
        public function Insert($post, &$error)
        {
            $data = array();
            foreach ($post as &$value)
            {
                $data = $data + $value;
            }
            unset($value);

            $name = urldecode($data['name']);
            $this->ValidateSiteName($name, $error);

            $description='';
            if (array_key_exists("description",$data))
            {
                $description = urldecode($data['description']);
                $this->common->ValidateLength("Description", $description, 200, $error);
            }

            $coordinates='';
            if (array_key_exists("coordinates",$data))
            {
                $coordinates = urldecode($data['coordinates']);
                $this->common->ValidateLength("Coordinates", $coordinates, 30, $error);
            }

            if ($error->valid)
            {
                $sql = 'INSERT INTO site (name, description, coordinates) VALUES (:name, :description, :coordinates)';
                $this->common->ExecuteCommand($sql,array(':name'=>$name,':description'=>$description,':coordinates'=>$coordinates),$error);
                if ($error->valid)
                {
                    $id= $this->GetIdFromName($name,$error);
                    return $this->GetById($id,$error);
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
            return $this->common->ExecuteCommand($siteSql,null, $error);
        }

        /**
         * Short Get a site by id
         * @param $id    int The unique identifier of the site.
         * @param $error ErrorResponse The errors if any.
         * @return Site the array of all sites with the given id.
         * An error is returned if the site is not known.
         */
        public function GetById($id,& $error)
        {
            if (!$this->Exists($id))
            {
                $error->AddNewError("Error", "Unknown site (id:$id).");
            }

            $siteSql = "SELECT * FROM site where id=:id";
            return $this->common->ExecuteCommand($siteSql,array(':id'=>$id), $error);
        }

        /**
         * Short Insert a Site into the system
         * @param $id         int the Unique id of the site.
         * @param $put        Array The put data
         * @param $error      ErrorResponse The errors accumulated for this call.
         * @return Site The inserted item.
         */
        public function Update($id, $put, &$error)
        {
            $data = array();
            foreach ($put as &$value)
            {
                $data = $data + $value;
            }
            unset($value);

            $name = urldecode($data['name']);
            $description = urldecode($data['description']);
            $coordinates = urldecode($data['coordinates']);

            $this->ValidateSiteName($name, $error, $id);
            $this->common->ValidateLength("Description", $description, 200, $error);
            $this->common->ValidateLength("Coordinates", $coordinates, 30, $error);
            if ($error->valid)
            {
                $sql='UPDATE site SET name=:name, description=:description, coordinates=:coordinates WHERE id=:id';
                $params = array(':name'=>$name,':description'=>$description,':coordinates'=>$coordinates,':id'=>$id);
                $this->common->ExecuteCommand($sql,$params,$error);
                return $this->GetById($id,$error);
            }

            return Array();
        }
    }

?>