<?php
    require_once 'ErrorResponse.php';
    require_once 'Site.php';
    require_once 'Trench.php';
    require_once 'Common.php';
    //$db = new mysqli("localhost", "my_user", "my_password", "world");

    /**
     * Class Find
     */
    class Find
    {

        // The database connection used to query the database.
        private $database;
        private $trench;
        private $site;
        private $common;

        /**
         * Short Creates a new class to handle finds.
         * @param $db     mysqli the database connection to use.
         * @param $trench Trench The Trench handling object
         * @param $site   Site The site handling object.
         * @param $common Common The common validation.
         * @return Find A new instance of the find object.
         */
        public function __construct($db, $site, $trench, $common)
        {
            $this->database = $db;
            $this->site = $site;
            $this->trench = $trench;
            $this->common = $common;
        }

        /** Short Get all the finds in the system.
         * @param $error ErrorResponse The accumulated errors for the current call.
         * @return array All the finds stored
         */
        public function GetAll($error)
        {
            $sql = $findsSql = "SELECT * FROM find";
            $finds = $this->ProcessFinds($sql, $error);;
            return $finds;
        }

        /**
         * Short Determine if a find exists.
         * @param $id int the unique id of the find.
         * @return bool true if the find exists.
         */
        private function Exists($id)
        {
            $findSql = "SELECT * FROM find WHERE id=" . $id;
            $findQuery = $this->database->query($findSql);

            return $findQuery->num_rows > 0;
        }

        /**
         * Short Get a find by id
         * @param $id    int The unique identifier of the find.
         * @param $error ErrorResponse The errors if any.
         * @return array the array of all finds with the given id.
         * An error is returned if the find is not known.
         */
        public function GetById($id, $error)
        {
            if (!$this->Exists($id))
            {
                $error->AddNewError("Error", "Unknown find (id:$id)");
            }

            $sql = $findsSql = "SELECT * FROM find where id=" . $id;

            $finds =  $this->ProcessFinds($sql, $error);
            return $finds;
        }

        /**
         * Short Gets an array of all find.
         * @param $sql   string The SQL query.
         * @param $error ErrorResponse The errors if any.
         * @return array the array of all finds for the given query.
         * if the find id is unknown an error is returned.
         */
        public function ProcessFinds($sql, $error)
        {
            $finds = array();
            $trenchSiteMapping = $this->trench->GetTrenchSiteMap($error);
            $trenchNames = $this->trench->GetNamesById($error);
            $siteNames = $this->site->GetNamesById($error);

            if ($error->valid)
            {
                $findQuery = $this->database->query($sql);
                if ($findQuery)
                {
                    while ($findRow = $findQuery->fetch_array(MYSQLI_ASSOC))
                    {
                        $findRow['siteName'] = $siteNames[$trenchSiteMapping[$findRow['trenchId']]];
                        $findRow['trenchName'] = $trenchNames[$findRow['trenchId']];
                        array_push($finds, $findRow);
                    }
                }
                               return $finds;
            }

            return Array();
        }

        /**
         * Short Insert a new find.
         * @param $post  Array The data to create the find from.
         * @param $error ErrorResponse The errors if any.
         * @return array The created find.
         */
        public function Insert($post, $error)
        {
            $data = array();
            foreach ($post as &$value)
            {
                $data = $data + $value;
            }
            unset($value);

            $trenchId = $this->common->Recode($data['trenchId']);
            $findNumber = $this->common->Recode($data['findNumber']);

            $context='';
            if (array_key_exists("context",$data))
            {
                $context = $this->common->Recode($data['context']);
                $this->common->ValidateLength("context", $context, 200, $error);
            }

            $numberOfSherds='';
            if (array_key_exists("numSherds",$data))
            {
                $numberOfSherds = $this->common->Recode($data['numSherds']);
                $this->common->ValidateNumber("Number of sherds",$numberOfSherds,$error);
                $this->common->ValidateLength("Number of sherds", $numberOfSherds, 200, $error);
            }

            $coordinates='';
            if (array_key_exists("coordinates",$data))
            {
                $coordinates = $this->common->Recode($data['coordinates']);
                $this->common->ValidateLength("coordinates", $coordinates, 200, $error);
            }

            $sherdType='';
            if (array_key_exists("sherdType",$data))
            {
                $sherdType = $this->common->Recode($data['sherdType']);
                $this->common->ValidateLength("sherdType", $sherdType, 200, $error);
            }

            $fabricType='';
            if (array_key_exists("fabricType",$data))
            {
                $fabricType = $this->common->Recode($data['fabricType']);
                $this->common->ValidateLength("fabricType", $fabricType, 200, $error);
            }

            $fabricTypeCode='';
            if (array_key_exists("fabricTypeCode",$data))
            {
                $fabricTypeCode = $this->common->Recode($data['fabricTypeCode']);
                $this->common->ValidateLength("fabricTypeCode", $fabricTypeCode, 200, $error);
            }

            $wareType='';
            if (array_key_exists("wareType",$data))
            {
                $wareType = $this->common->Recode($data['wareType']);
                $this->common->ValidateLength("wareType", $wareType, 200, $error);
            }
            $baseType='';
            if (array_key_exists("baseType",$data))
            {
                $baseType = $this->common->Recode($data['baseType']);
                $this->common->ValidateLength("baseType", $baseType, 200, $error);
            }
            $rimType='';
            if (array_key_exists("rimType",$data))
            {
                $rimType = $this->common->Recode($data['rimType']);
                $this->common->ValidateLength("rimType",$rimType, 200, $error);
            }
            $fabricColour='';
            if (array_key_exists("fabricColour",$data))
            {
                $fabricColour = $this->common->Recode($data['fabricColour']);
                $this->common->ValidateLength("fabricColour", $fabricColour, 200, $error);
            }
            $construction='';
            if (array_key_exists("construction",$data))
            {
                $construction = $this->common->Recode($data['construction']);
                $this->common->ValidateLength("construction", $construction, 200, $error);
            }

            $height='';
            if (array_key_exists("height",$data))
            {
                $height = $this->common->Recode($data['height']);
                $this->common->ValidateLength("height", $height, 200, $error);
            }
            $width='';
            if (array_key_exists("width",$data))
            {
                $width = $this->common->Recode($data['width']);
                $this->common->ValidateLength("width", $width, 200, $error);
            }
            $thickness='';
            if (array_key_exists("thickness",$data))
            {
                $thickness = $this->common->Recode($data['thickness']);
                $this->common->ValidateLength("thickness", $thickness, 200, $error);
            }

            $weight='';
            if (array_key_exists("Weight",$data))
            {
                $weight = $this->common->Recode($data['Weight']);
                $this->common->ValidateLength("Weight", $weight, 200, $error);
            }
            $rimDiameter='';
            if (array_key_exists("rimDiameter",$data))
            {
                $rimDiameter = $this->common->Recode($data['rimDiameter']);
                $this->common->ValidateLength("rimDiameter", $rimDiameter, 200, $error);
            }
            $baseDiameter='';
            if (array_key_exists("baseDiameter",$data))
            {
                $baseDiameter = $this->common->Recode($data['baseDiameter']);
                $this->common->ValidateLength("baseDiameter", $baseDiameter, 200, $error);
            }
            $temperQuality='';
            if (array_key_exists("temperQuality",$data))
            {
                $temperQuality = $this->common->Recode($data['temperQuality']);
                $this->common->ValidateLength("temperQuality", $temperQuality, 200, $error);
            }

            $surfaceTreatment='';
            if (array_key_exists("surfaceTreatment",$data))
            {
                $surfaceTreatment = $this->common->Recode($data['surfaceTreatment']);
                $this->common->ValidateLength("surfaceTreatment", $surfaceTreatment, 200, $error);
            }
            $temperType='';
            if (array_key_exists("temperType",$data))
            {
                $temperType = $this->common->Recode($data['temperType']);
                $this->common->ValidateLength("temperType", $temperType, 200, $error);
            }

            $manufacture='';
            if (array_key_exists("manufacture",$data))
            {
                $manufacture = $this->common->Recode($data['manufacture']);
                $this->common->ValidateLength("manufacture", $manufacture, 200, $error);
            }
            $sherdCondition='';
            if (array_key_exists("sherdCondition",$data))
            {
                $sherdCondition = $this->common->Recode($data['sherdCondition']);
                $this->common->ValidateLength("sherdCondition", $sherdCondition, 200, $error);
            }
            $decoration='';
            if (array_key_exists("decoration",$data))
            {
                $decoration = $this->common->Recode($data['decoration']);
                $this->common->ValidateLength("decoration", $decoration, 200, $error);
            }

            $analysisType='';
            if (array_key_exists("scientificAnalysisType",$data))
            {
                $analysisType = $this->common->Recode($data['scientificAnalysisType']);
                $this->common->ValidateLength("scientificAnalysisType", $analysisType, 200, $error);
            }
            $sampleNumber='';
            if (array_key_exists("sampleNumber",$data))
            {
                $sampleNumber = $this->common->Recode($data['sampleNumber']);
                $this->common->ValidateLength("sampleNumber", $sampleNumber, 200, $error);
            }
            $minimumNumberOfVessels='';
            if (array_key_exists("mnvRepresented",$data))
            {
                $minimumNumberOfVessels = $this->common->Recode($data['mnvRepresented']);
                $this->common->ValidateLength("mnvRepresented", $minimumNumberOfVessels, 200, $error);
            }
            $residues='';
            if (array_key_exists("residues",$data))
            {
                $residues = $this->common->Recode($data['residues']);
                $this->common->ValidateLength("residues", $residues, 200, $error);
            }

            $notes='';
            if (array_key_exists("notes",$data))
            {
                $notes = $this->common->Recode($data['notes']);
                $this->common->ValidateLength("notes", $notes, 1000, $error);
            }

            if ($error->valid)
            {
                $sql = "INSERT INTO find (trenchId,findNumber,context,numberOfSherds,coordinates,sherdType,fabricType,fabricTypeCode,wareType,baseType,rimType,fabricColour,construction,height,width,thickness,weight,rimDiameter,baseDiameter,surfaceTreatment,temperType,temperQuality,manufacture,sherdCondition,decoration,analysisType,sampleNumber,minimumNumberOfVessels,residues,notes)VALUES('$trenchId','$findNumber','$context','$numberOfSherds','$coordinates','$sherdType','$fabricType','$fabricTypeCode','$wareType','$baseType','$rimType','$fabricColour','$construction','$height','$width','$thickness','$weight','$rimDiameter','$baseDiameter','$surfaceTreatment','$temperType','$temperQuality','$manufacture','$sherdCondition','$decoration','$analysisType','$sampleNumber','$minimumNumberOfVessels','$residues','$notes')";
                $insertQuery = $this->database->query($sql);
                if ($insertQuery != false)
                {
                    return $this->GetById($this->database->insert_id, $error);
                }
            }

            return array();
        }

        /**
         * Short Insert a new find.
         * @param £id    int the unique identifier for the find.
         * @param $put   Array The data to create the find from.
         * @param $error ErrorResponse The errors if any.
         * @return array The created find.
         */
        public function Update($id, $put, $error)
        {
            $data = array();

            if (!$this->Exists($id, $error))
            {
                $error->AddNewError("Error", "Unable to update find (id:$id) the find is unknown");
            }

            foreach ($put as &$value)
            {
                $data = $data + $value;
            }
            unset($value);

            $trenchId = $this->common->Recode($data['trenchId']);
            $findNumber = $this->common->Recode($data['findNumber']);

            $context='';
            if (array_key_exists("context",$data))
            {
                $context = $this->common->Recode($data['context']);
                $this->common->ValidateLength("context", $context, 200, $error);
            }

            $numberOfSherds='';
            if (array_key_exists("numSherds",$data))
            {
                $numberOfSherds = $this->common->Recode($data['numSherds']);
                $this->common->ValidateNumber("Number of sherds",$numberOfSherds,$error);
                $this->common->ValidateLength("Number of sherds", $numberOfSherds, 200, $error);
            }

            $coordinates='';
            if (array_key_exists("coordinates",$data))
            {
                $coordinates = $this->common->Recode($data['coordinates']);
                $this->common->ValidateLength("coordinates", $coordinates, 200, $error);
            }

            $sherdType='';
            if (array_key_exists("sherdType",$data))
            {
                $sherdType = $this->common->Recode($data['sherdType']);
                $this->common->ValidateLength("sherdType", $sherdType, 200, $error);
            }

            $fabricType='';
            if (array_key_exists("fabricType",$data))
            {
                $fabricType = $this->common->Recode($data['fabricType']);
                $this->common->ValidateLength("fabricType", $fabricType, 200, $error);
            }

            $fabricTypeCode='';
            if (array_key_exists("fabricTypeCode",$data))
            {
                $fabricTypeCode = $this->common->Recode($data['fabricTypeCode']);
                $this->common->ValidateLength("fabricTypeCode", $fabricTypeCode, 200, $error);
            }

            $wareType='';
            if (array_key_exists("wareType",$data))
            {
                $wareType = $this->common->Recode($data['wareType']);
                $this->common->ValidateLength("wareType", $wareType, 200, $error);
            }
            $baseType='';
            if (array_key_exists("baseType",$data))
            {
                $baseType = $this->common->Recode($data['baseType']);
                $this->common->ValidateLength("baseType", $baseType, 200, $error);
            }
            $rimType='';
            if (array_key_exists("rimType",$data))
            {
                $rimType = $this->common->Recode($data['rimType']);
                $this->common->ValidateLength("rimType",$rimType, 200, $error);
            }
            $fabricColour='';
            if (array_key_exists("fabricColour",$data))
            {
                $fabricColour = $this->common->Recode($data['fabricColour']);
                $this->common->ValidateLength("fabricColour", $fabricColour, 200, $error);
            }
            $construction='';
            if (array_key_exists("construction",$data))
            {
                $construction = $this->common->Recode($data['construction']);
                $this->common->ValidateLength("construction", $construction, 200, $error);
            }

            $height='';
            if (array_key_exists("height",$data))
            {
                $height = $this->common->Recode($data['height']);
                $this->common->ValidateLength("height", $height, 200, $error);
            }
            $width='';
            if (array_key_exists("width",$data))
            {
                $width = $this->common->Recode($data['width']);
                $this->common->ValidateLength("width", $width, 200, $error);
            }
            $thickness='';
            if (array_key_exists("thickness",$data))
            {
                $thickness = $this->common->Recode($data['thickness']);
                $this->common->ValidateLength("thickness", $thickness, 200, $error);
            }

            $weight='';
            if (array_key_exists("Weight",$data))
            {
                $weight = $this->common->Recode($data['Weight']);
                $this->common->ValidateLength("Weight", $weight, 200, $error);
            }
            $rimDiameter='';
            if (array_key_exists("rimDiameter",$data))
            {
                $rimDiameter = $this->common->Recode($data['rimDiameter']);
                $this->common->ValidateLength("rimDiameter", $rimDiameter, 200, $error);
            }
            $baseDiameter='';
            if (array_key_exists("baseDiameter",$data))
            {
                $baseDiameter = $this->common->Recode($data['baseDiameter']);
                $this->common->ValidateLength("baseDiameter", $baseDiameter, 200, $error);
            }
            $temperQuality='';
            if (array_key_exists("temperQuality",$data))
            {
                $temperQuality = $this->common->Recode($data['temperQuality']);
                $this->common->ValidateLength("temperQuality", $temperQuality, 200, $error);
            }

            $surfaceTreatment='';
            if (array_key_exists("surfaceTreatment",$data))
            {
                $surfaceTreatment = $this->common->Recode($data['surfaceTreatment']);
                $this->common->ValidateLength("surfaceTreatment", $surfaceTreatment, 200, $error);
            }
            $temperType='';
            if (array_key_exists("temperType",$data))
            {
                $temperType = $this->common->Recode($data['temperType']);
                $this->common->ValidateLength("temperType", $temperType, 200, $error);
            }

            $manufacture='';
            if (array_key_exists("manufacture",$data))
            {
                $manufacture = $this->common->Recode($data['manufacture']);
                $this->common->ValidateLength("manufacture", $manufacture, 200, $error);
            }
            $sherdCondition='';
            if (array_key_exists("sherdCondition",$data))
            {
                $sherdCondition = $this->common->Recode($data['sherdCondition']);
                $this->common->ValidateLength("sherdCondition", $sherdCondition, 200, $error);
            }
            $decoration='';
            if (array_key_exists("decoration",$data))
            {
                $decoration = $this->common->Recode($data['decoration']);
                $this->common->ValidateLength("decoration", $decoration, 200, $error);
            }

            $analysisType='';
            if (array_key_exists("scientificAnalysisType",$data))
            {
                $analysisType = $this->common->Recode($data['scientificAnalysisType']);
                $this->common->ValidateLength("scientificAnalysisType", $analysisType, 200, $error);
            }
            $sampleNumber='';
            if (array_key_exists("sampleNumber",$data))
            {
                $sampleNumber = $this->common->Recode($data['sampleNumber']);
                $this->common->ValidateLength("sampleNumber", $sampleNumber, 200, $error);
            }
            $minimumNumberOfVessels='';
            if (array_key_exists("mnvRepresented",$data))
            {
                $minimumNumberOfVessels = $this->common->Recode($data['mnvRepresented']);
                $this->common->ValidateLength("mnvRepresented", $minimumNumberOfVessels, 200, $error);
            }
            $residues='';
            if (array_key_exists("residues",$data))
            {
                $residues = $this->common->Recode($data['residues']);
                $this->common->ValidateLength("context", $residues, 200, $error);
            }
            $notes='';
            if (array_key_exists("notes",$data))
            {
                $notes = $this->common->Recode($data['notes']);
                $this->common->ValidateLength("notes", $notes, 1000, $error);
            }

            if ($error->valid)
            {

                $sql = "UPDATE find SET  trenchId='$trenchId',findNumber='$findNumber',context='$context',numberOfSherds='$numberOfSherds',coordinates='$coordinates',sherdType='$sherdType',fabricType='$fabricType',fabricTypeCode='$fabricTypeCode',wareType='$wareType',baseType='$baseType',rimType='$rimType',fabricColour='$fabricColour',construction='$construction',height='$height',width='$width',thickness='$thickness',weight='$weight',rimDiameter='$rimDiameter',baseDiameter='$baseDiameter',surfaceTreatment='$surfaceTreatment',temperType='$temperType',temperQuality='$temperQuality',manufacture='$manufacture',sherdCondition='$sherdCondition',decoration='$decoration',analysisType='$analysisType',sampleNumber='$sampleNumber',minimumNumberOfVessels='$minimumNumberOfVessels',residues='$residues',notes='$notes' WHERE id='$id'";
                $insertQuery = $this->database->query($sql);
                if ($insertQuery != false)
                {
                    return $this->GetById($id, $error);
                }
            }
            else
            {
                $error->AddNewError("CRITICAL", $this->database->error);
            }

            return Array();
        }

        /**
         * Short Delete a find from the database
         * @param $id    int the unique identifier for the find.
         * @param $error ErrorResponse The response which we are generating
         */
        function Delete($id, $error)
        {

            if ($this->Exists($id))
            {

                $sql = 'Delete from find WHERE id=' . $id;
                $deleteQuery = $this->database->query($sql);
                if ($deleteQuery != false)
                {
                   return;
                }
                else
                {
                    $error->AddNewError("CRITICAL", $this->database->error);
                }
            }
            else
            {
                $error->AddNewError("Error", "Unable to delete find (id:$id) the find is not known");
            }
        }
    }

    /**
     * Short Validate that the provided string is a number
     * @param $name string the number
     * @return bool true if the item is valid.
     */
    function ValidateNumber($name)
    {
        return is_numeric($name);
    }

    /**
     * Short Validate that the length of the string is suitable for the database
     * @param $name   string the name of the variable we are validating
     * @param $string string the string we are validating
     * @param $length int the maximum length of the string
     * @return bool true if the item is valid.
     */
    function ValidateLength($name, $string, $length)
    {
        if (strlen($string) > $length)
        {
            echo "The ($name) value ($string) is too long the maximum length is $length";

            return false;
        }

        return true;
    }

?>