<?php
    require_once 'Site.php';
    require_once 'Trench.php';
    require_once 'Classes/PHPExcel.php';

    /**
     * Class Export
     */
    class Export
    {

        private $site;
        private $trench;

        /**
         * Short Create a new export class
         * @param $site   Site The site access class
         * @param $trench Trench The trench access class
         * @return Export The Export class.
         */
        public function __construct($site, $trench)
        {
            $this->trench = $trench;
            $this->site = $site;
        }

        /**
         * Short Export all finds from a trench to a single Excel worksheet
         * @param $id     int The unique identifier of the trench.
         * @param $error  ErrorResponse The errors for this request.
         * @return PHPExcel The PHPExcel object which contains the generated data.
         */
        public function Trench($id, $error)
        {
            $trenchSiteMap = $this->trench->GetTrenchSiteMap($error);
            $siteId = $trenchSiteMap[$id];
            $siteName = $this->site->GetName($siteId, $error);

            $trenchName = $this->trench->GetName($id, $error);
            $objPHPExcel = new PHPExcel();
            if ($error->valid)
            {

            $objPHPExcel->getProperties()->setCreator("Project Dawn")->setLastModifiedBy("Project Dawn")->setTitle("Exported for $siteName trench $trenchName")->setSubject("")->setDescription("Find data for $siteName trench $trenchName exported " . date('H:i:s'))->setKeywords("")->setCategory("");

            $sheetIndex = 0;
            $objPHPExcel->setActiveSheetIndex($sheetIndex);
            $objPHPExcel->getActiveSheet()->setTitle('Trench '.$trenchName);
            $finds = $this->trench->GetFinds($id,$error);
            $this->GenerateTable($objPHPExcel, $sheetIndex, $finds);
            }
            return $objPHPExcel;
        }

        /**
         * Short Write the provided data to the Excel spreadsheet
         * @param $objPHPExcel PHPExcel The Php object used to write the excel file.
         * @param $sheetIndex  int the index of the sheet which the data should be written to.
         * @param $finds       Array The list of finds to write to the excel document.
         */
        private function GenerateTable($objPHPExcel, $sheetIndex, $finds)
        {
            if (count($finds) > 0)
            {
                $this->SetFindHeaderRow($objPHPExcel, $sheetIndex, $finds[0]);
                for ($findIndex = 0; $findIndex < count($finds); $findIndex++)
                {
                    $this->SetFindRow($objPHPExcel, $sheetIndex, $findIndex, $finds[$findIndex]);
                }
            }
        }

        /**
         * Short Set the values for a single find
         * @param $objPHPExcel PHPExcel The Php object used to write the excel file.
         * @param $sheetIndex  int the index of the sheet which the data should be written to.
         * @param $data        Array the data for the find to write
         */
        private function SetFindHeaderRow($objPHPExcel, $sheetIndex, $data)
        {
            $objPHPExcel->setActiveSheetIndex($sheetIndex);
            $keys = array_keys($data);
            for ($dataIndex = 0; $dataIndex < count($data); $dataIndex++)
            {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($dataIndex, 1, $keys[$dataIndex]);
            }
        }

        /**
         * Short Set the values for a single find
         * @param $objPHPExcel PHPExcel The Php object used to write the excel file.
         * @param $sheetIndex  int the index of the sheet which the data should be written to.
         * @param $rowIndex    int The index of the row which the data should be written to.
         * @param $data        Array the data for the find to write
         */
        private function SetFindRow($objPHPExcel, $sheetIndex, $rowIndex, $data)
        {
            $objPHPExcel->setActiveSheetIndex($sheetIndex);
            $keys = array_values($data);
            for ($dataIndex = 0; $dataIndex < count($data); $dataIndex++)
            {
                 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($dataIndex, $rowIndex + 2, $keys[$dataIndex]);
            }
        }

        /**
         * Short Export all finds from a site to a single Excel worksheet
         * @param $id     int The unique identifier of the site.
         * @param $error  ErrorResponse The errors for this request.
         * @return PHPExcel The PHPExcel object which contains the generated data.
         */
        public function Site($id, $error)
        {

            $siteName = $this->site->GetName($id, $error);
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("Project Dawn")->setLastModifiedBy("Project Dawn")->setTitle("Exported for $siteName")->setSubject("")->setDescription("Find data for " . $siteName . " exported " . date('H:i:s'))->setKeywords("")->setCategory("");
            $sheetIndex = 0;
            $objPHPExcel->setActiveSheetIndex($sheetIndex);
            $objPHPExcel->getActiveSheet()->setTitle($siteName);
            $finds = $this->site->GetFinds($id, $error);
            $this->GenerateTable($objPHPExcel, $sheetIndex, $finds);

            return $objPHPExcel;
        }
    }
?>