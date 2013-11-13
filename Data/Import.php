<?php
require_once 'Error.php';
require_once 'ErrorResponse.php';
require_once 'Site.php';
require_once 'Trench.php';
require_once 'Find.php';
require_once 'Classes/PHPExcel.php';
class Import
{

    private $filename;
    private $workbook;
    private $site;
    private $trench;
    private $find;
    private $columnMapping;
private $columnNames;
    // This is an array of arrays containing the aliases of field name to look for in the column names.
    private $FieldAlias = array();

    /**
     * Short Create a new Importer.
     * @param $site   Site The site handler to use.
     * @param $trench Trench The trench handler to use.
     * @param $find   Find The find handler to use.
     */
    function __construct($site, $trench, $find)
    {
        $this->site = $site;
        $this->trench = $trench;
        $this->find = $find;
        $this->FieldAlias['findNumber'] = array("FindNumber", "Find Number", "Find No", "Find No.", "FindNo", "FindNo.");
        $this->FieldAlias['context'] = array("Context");
        $this->FieldAlias['numberOfSherds'] = array("Number Of Sherds", "no of sherds", "NumberOfSherds", "SherdCount", "Sherd Count");
        $this->FieldAlias['coordinates'] = array("Coordinates", "coords", "gps", "gps coordinates");
        $this->FieldAlias['sherdType'] = array("SherdType", "sherd type", "Type of Sherd");
        $this->FieldAlias['fabricType'] = array("Fabric type", "FabricType", "TypeOfFabric", "Type of Fabric");
        $this->FieldAlias['fabricTypeCode'] = array("fabricTypeCode", "Fabric type no", "Fabric type code"); // or match ".+ fabric type$" (case insesative)
        $this->FieldAlias['wareType'] = array("wareType");
        $this->FieldAlias['baseType'] = array("baseType"); // "Form of rim/base" &&  "Sherd type" == "base"
        $this->FieldAlias['rimType'] = array("Form of rim", "rimType"); // "Form of rim/base" &&  "Sherd type" == "rim"
        $this->FieldAlias['fabricColour'] = array("Fabric colour", "Fabric Color", "Fabric colour (ext,core, int)", "FabricColour", "FabricColor");
        $this->FieldAlias['construction'] = array("Construction");
        $this->FieldAlias['height'] = array("Height");
        $this->FieldAlias['width'] = array("Width");
        $this->FieldAlias['thickness'] = array("thickness", "Thickness (mm)", "Wall thickness");
        $this->FieldAlias['weight'] = array("Weight");
        $this->FieldAlias['rimDiameter'] = array("rimDiameter", "rim Diameter"); //Diam' r/b (est) +"Sherd type" == "rim" or "base" or "Diameter (est)"+"Sherd type" == "rim"or "Diameter (est) mm"+"Sherd type" == "rim"
        $this->FieldAlias['baseDiameter'] = array("baseDiameter", "base Diameter"); //Diam' r/b (est) +"Sherd type" == "base" or "Diameter (est)" +"Sherd type" == "base"
        $this->FieldAlias['surfaceTreatment'] = array("Surface treatment", "surfaceTreatment");
        $this->FieldAlias['temperType'] = array("inclusions/tempers", "Temper", "TemperType");
        $this->FieldAlias['temperQuality'] = array("TemperQuality", "Temper Quality");
        $this->FieldAlias['manufacture'] = array("Manufacture");
        $this->FieldAlias['sherdCondition'] = array("Sherd condition", "sherdCondition");
        $this->FieldAlias['decoration'] = array("Surface decoration", "Surface dec'n", "decoration");
        $this->FieldAlias['analysisType'] = array("Analysis Type", "analysisType");
        $this->FieldAlias['sampleNumber'] = array("Sample Number", "sampleNumber");
        $this->FieldAlias['minimumNumberOfVessels'] = array("minimumNumberOfVessels", "MNV", "minimum Number Of Vessels");
        $this->FieldAlias['residues'] = array("residue/Soot", "Residues (t&p)", "residues");
        $this->FieldAlias['siteName'] = array("SiteName", "Site Name", "Site of Origin");
        $this->FieldAlias['trenchName'] = array("TrenchName", "Trench Name", "Trench");
        $this->FieldAlias['notes'] = array("Notes", "Comments", " additional notes and Comments ", "Additional notes");
    }

    function Import($file, $error)
    {
        $this->LoadWorkBook($file);
        $columnNames = $this->GetColumnNames();
        $this->generateColumnMapping($columnNames);
        $siteIds = $this->generateSites($error);
        $this->generateTrenches($siteIds, $error);
        $this->ImportFind($error);
        unlink($file);
    }

    function LoadWorkBook($file)
    {
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(true);
        $this->workbook = $objReader->load($file);
        $this->filename = $file;
    }

    private function GetColumnNames()
    {
        $this->columnNames = array();

        // Retrieve the current active worksheet
        $objWorksheet = $this->workbook->getActiveSheet();

        // Iterate through all occupied cells in first row getting the contents (using column index as a key);
        $rowIterator = $objWorksheet->getRowIterator();
        $row = $rowIterator->current();
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);

        foreach ($cellIterator as $cell) {
            $this->columnNames[PHPExcel_Cell::columnIndexFromString($cell->getColumn())] = $cell->getValue();
        }
    }

    private function generateColumnMapping()
    {

        $mapping = array();
        foreach (array_keys($this->FieldAlias) as $columnAliases) {
            $column = -1;
            foreach ($this->FieldAlias[$columnAliases] as $alias) {
                foreach (array_keys($this->columnNames) as $column) {
                    if (strcasecmp($this->columnNames[$column], $alias) == 0) {
                        $mapping[$columnAliases] = $column;
                    }
                }
            }
        }
        $this->columnMapping = $mapping;
    }

    private function generateColumnMappingForHeadings($headings)
    {

        $mapping ='0';
                    $column = -1;
            foreach ($headings as $alias) {
                foreach (array_keys($this->columnNames) as $column) {
                    if (strcasecmp($this->columnNames[$column], $alias) == 0) {
                        $mapping = $column;
                    }
                }
            }

        return $mapping;
    }

    private function generateSites($error)
    {
        $siteIds = array();
        $column = $this->columnMapping['siteName'];
        if ($column != 0) {
            $siteNames = $this->GetDistinctValues($column);
            foreach ($siteNames as $siteName) {
                if (!$this->site->NameExists($siteName)) {
                    $json = '[{"name":"' . $siteName . '"}]';
                    $sitePost = json_decode($json, true);
                    $siteData = $this->site->Insert($sitePost, $error);

                    if ($error->valid) {

                        $site = $siteData[0];
                        $siteIds[$siteName] = $site["id"];
                        echo "Added site " . $siteName . " (" . $site["id"] . ")\r\n";
                    } else {
                        echo "Failed to add site " . $siteName . " (" . $error . ")\r\n";
                    }
                } else {
                    $siteIds[$siteName] = $this->site->GetIdFromName($siteName, $error);
                }
            }
        }

        return $siteIds;
    }

    private function GetDistinctValues($column)
    {
        $columnName = PHPExcel_Cell::stringFromColumnIndex($column - 1);
        $objWorksheet = $this->workbook->getActiveSheet();
        $lastRow = $objWorksheet->getHighestRow();
        $rows = array();
        for ($row = 2; $row <= $lastRow; $row++) {
            array_push($rows, $objWorksheet->getCell($columnName . $row)->GetValue());
        }

        return array_unique($rows);
    }

    private function generateTrenches($siteIds, $error)
    {
        $trenchIds = array();
        $siteNameColumn = $this->columnMapping['siteName'];
        $trenchNameColumn = $this->columnMapping['trenchName'];
        if ($siteNameColumn != 0) {
            //------------------------------------------------------------
            $siteTrenchPair = $this-> GenerateSiteTrenchPair($siteNameColumn, $trenchNameColumn);
            foreach (array_keys($siteTrenchPair) as $siteName) {
                foreach ($siteTrenchPair[$siteName] as $trenchName) {
                    if (!$this->trench->NameExists($trenchName, $siteName, $error))
                    {
                        $siteId = $siteIds[$siteName];
                        $trenchPost = json_decode('[{"name":"' . $trenchName . '"},{"sites":"' . $siteId . '"}]', true);
                        $trenchData = $this->trench->Insert($trenchPost, $error);

                        if ($error->valid)
                        {
                            $trenchIds[$trenchName] = $trenchData[0]['id'];
                            echo "Added trench " . $trenchName . " (" . $trenchData[0]['id'] . ") to site ".$trenchData[0]['siteName']." \r\n";
                        }
                        else {
                            echo "Failed to add trench " . $trenchName . " (" . $error . ")\r\n";
                        }
                    }
                    else
                    {  $trench = $this->trench->GetFromName($siteName, $trenchName, $error);
                        $trenchIds[$trenchName] = $trench[0]['id'];
                        echo "located existing trench ".$trenchName." id ".$trench[0]['id'];

                    }
                    //----------------------------------------------------------
                }
            }
        }
        return $trenchIds;
    }

    function GenerateSiteTrenchPair($siteColumn, $trenchColumn)
    {
        $siteTrenchPair = array();
        $siteColumnName = PHPExcel_Cell::stringFromColumnIndex($siteColumn - 1);
        $trenchColumnName = PHPExcel_Cell::stringFromColumnIndex($trenchColumn - 1);

        var_dump ($trenchColumnName);
        $objWorksheet = $this->workbook->getActiveSheet();
        $lastRow = $objWorksheet->getHighestRow();

        for ($row = 2; $row <= $lastRow; $row++) {
            $siteName = $objWorksheet->GetCell($siteColumnName . $row)->getFormattedValue();
            if ($trenchColumnName != null && $trenchColumn != "")
            {
            $trenchName = $objWorksheet->GetCell($trenchColumnName . $row)->getFormattedValue();
            }
            else
            {
                $trenchName = "Unknown";
            }
            if (!in_array($siteName, array_keys($siteTrenchPair))) {
                $siteTrenchPair[$siteName] = array();
            }
            array_push($siteTrenchPair[$siteName], $trenchName);
        }

        foreach (array_keys($siteTrenchPair) as $siteNameKey) {
            $siteTrenchPair[$siteNameKey] = array_unique($siteTrenchPair[$siteNameKey]);
        }
        return $siteTrenchPair;
    }

    function ImportFind($error)
    {
        $objWorksheet = $this->workbook->getActiveSheet();
        $lastRow = $objWorksheet->getHighestRow();



        for ($row = 2; $row <= $lastRow; $row++) {
            $findNumber =0;
            $siteName ='';
            $json ="[";
            foreach (array_keys($this->columnMapping) as $column)
            {
                if ($this->columnMapping[$column]) {

                    if (!$this->endsWith('[',$json))
                    {
                        $json = $json.",";
                    }
                    $value=$this->GetColumn($row,$this->columnMapping[$column]);
                    $json = $json.'{"'.$column.'": "'.urlencode($value).'"}';

                    if ($column == 'findNumber')
                    {
                        $findNumber = $value;
                    }
                    if ($column == 'siteName')
                    {
                        $siteName = $value;
                    }
                }
            }

            $trenchName = "Unknown";
            if (count($this->columnMapping['trenchName']) !=0)
            {
                $trenchNameInFile =$this->GetColumn($row,$this->columnMapping['trenchName']);
                if ($trenchNameInFile != null && $trenchNameInFile != '')
                {
                    $trenchName = $trenchNameInFile;
                }
            }
            $trenchIdData = $this->trench->GetFromName($siteName,$trenchName,$error);
            if ($error->valid)
            {
            $trenchId =$trenchIdData[0]['id'];
            $json = $json.',{"trenchId" : "'.urlencode($trenchId).'"}';
            }
            else{
                echo "Error looking up id for trench :".$error."\r\n";
                $error = new ErrorResponse();
            }


            // Special case look-ups.
            $sherdType = $this->GetColumn($row,$this->columnMapping['sherdType']);
            $diameterColumnId = $this->generateColumnMappingForHeadings(array("Diam' r/b (est)","Diameter (est)","Diameter (est) mm"));
            if (strcasecmp($sherdType,"rim")==0)
            {
                if (!$this->columnMapping['rimDiameter'])
                {
                    //rimDiameter Diam' r/b (est) +"Sherd type" == "rim" or "base" or "Diameter (est)"+"Sherd type" == "rim"or "Diameter (est) mm"+"Sherd type" == "rim"
                    if ($diameterColumnId !=0)
                    {
                            $json = $json.',{"rimDiameter" : "'.urlencode($this->GetColumn($row,$diameterColumnId)).'"}';
                    }
                }

                // "Form of rim/base" &&  "Sherd type" == "rim"
                if (!$this->columnMapping['rimType'])
                {
                    $json = $json.',{"rimType" : "'.urlencode($this->GetColumnByName($row,"Form of rim/base")).'"}';
                }
            }
            else if(strcasecmp($sherdType,"base")==0)
            {
                if (!$this->columnMapping['baseDiameter'])
                {


                    if ($diameterColumnId !=0)
                    {

                        $json = $json.',{"baseDiameter" : "'.urlencode($this->GetColumn($row,$diameterColumnId)).'"}';
                    }
                }

                // "Form of rim/base" &&  "Sherd type" == "rim"
                if (!$this->columnMapping['baseType'])
                {
                    $json = $json.',{"baseType" : "'.urlencode($this->GetColumnByName($row,"Form of rim/base")).'"}';
                }
            }

            //fabricTypeCode prepended by site name or other text.
            if (!$this->columnMapping['fabricTypeCode'])
            {
                foreach ($this->columnNames as $name)
                {
                    if (preg_match("/.+ [fF][aA][bB][rR][iI][cC] [tT][yY][pP][eE]$/",$name ))
                    {
                        $json = $json.',{"fabricTypeCode" : "'.urlencode($this->GetColumnByName($row,$name)).'"}';
                    }
                }
            }
            $json =$json."]";
            $findPost=json_decode($json,true);
            $finds = $this->find->Insert($findPost,$error);
            if ($error->valid)
            {
                echo "Added find " . $finds[0]["findNumber"] . " to trench ".$finds[0]["trenchName"]." at site".$finds[0]["siteName"]."\r\n";
            }
            else {
                echo "Failed to add find " . $findNumber." "."$error.\r\n";
            }
        }
    }

    function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    private function GetColumnByName($rowIndex, $Name)
    {
        $objWorksheet = $this->workbook->getActiveSheet();

        // Iterate through all occupied cells in first row getting the contents (using column index as a key);
        $rowIterator = $objWorksheet->getRowIterator();
        $row = $rowIterator->current();
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);

        foreach ($cellIterator as $cell) {
            if (strcasecmp($cell->getValue(), $Name)) {
                $columnName = $cell->getColumn();
                return $objWorksheet->getCell($columnName . $rowIndex)->getValue();
            }
        }
        return null;
    }

    private function GetColumn($rowIndex, $columnIndex)
    {
        $columnName = PHPExcel_Cell::stringFromColumnIndex($columnIndex - 1);
        if ($columnName != null && $columnName != '')
        {
        $objWorksheet = $this->workbook->getActiveSheet();
        return $objWorksheet->getCell($columnName . $rowIndex)->getValue();
        }
        return null;
    }
}
