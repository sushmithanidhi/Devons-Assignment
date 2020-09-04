<?php

namespace App\Service;


use App\Constants\Constants;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

/**
 * Class AdminService
 * @package Highlow\Service
 */
class HelperService
{

    public function getUniqueId($serverDetails){
//        foreach ($serverDetails as $serverData) {
            for($i=0;$i<count($serverDetails);$i++){
                $serverDetails[$i]['uniqueId'] = md5($serverDetails[$i]["model"] . '|' . $serverDetails[$i]["RAM"] . '|' . $serverDetails[$i]["HDD"] . '|' . $serverDetails[$i]["location"] . '|' . $serverDetails[$i]["price"]);
        }
        return $serverDetails;
    }

    /**
     * @param $filePath
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function convertXlsxToCsv($filePath)
    {
        $this->reader = IOFactory::createReader("Xlsx");
        $spreadsheet = $this->reader->load($filePath . '.xlsx');
        $writer = IOFactory::createWriter($spreadsheet, "Csv");
        $writer->setSheetIndex(0);   // Select which sheet to export.
        $writer->setDelimiter(',');  // Set delimiter.
        $writer->save($filePath . '.csv');
    }

    /**
     * Function to get the formatted exception.
     *
     * @param Exception $exception
     * @param bool      $expand
     *
     * @return string
     */
    public static function exceptionFormatter(Exception $exception, $expand = false)
    {
        return $expand
            ? sprintf("Error [%s]: File: %s\nMessage: %s\nTrace: %s\n", $exception->getLine(), $exception->getFile(), $exception->getMessage(), $exception->getTraceAsString())
            : sprintf("Error [File: (%s), Line: (%s)]: %s", $exception->getFile(), $exception->getLine(), $exception->getMessage());
    }

//    /**
//     * @param $file
//     * @param $slugger
//     * @return string
//     * @throws \Exception
//     */
//    public function storeXlsxFile($file, $slugger){
//            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
//            // this is needed to safely include the file name as part of the URL
//            $safeFilename = $slugger->slug($originalFilename);
//            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
//            try {
//                $file->move(
//                    Constants::FILE_PATH,
//                    Constants::FILE_NAME.'.xlsx'
//                );
//                return Constants::FILE_NAME.'.xlsx';
//            } catch (FileException $e) {
//                throw new \Exception("Error has been occured while storing the file".$e->getMessage());
//                return '';
//            }
//    }
}