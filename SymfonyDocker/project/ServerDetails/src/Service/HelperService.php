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
    public function __construct($redis = null,LoggerInterface $logger = null){
        $this->redis = $redis;
        $this->logger = $logger;
    }

    /**
     * @param $response
     */
    public function setRedisValue($response){
        $this->redis->set('serverDetails',json_encode($response));
    }

    /**
     * @param $serverDetails
     * @return mixed
     */
    public function getValueFromRedis(){
        if($this->redis->get('serverDetails')){
            return json_decode($this->redis->get('serverDetails'),true);
        }else {
            return false;
        }
    }

     /**
     * @param $serverDetails
     * @return array
     */
    public function getUniqueId($serverDetails){
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
}