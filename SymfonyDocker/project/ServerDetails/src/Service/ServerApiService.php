<?php

namespace App\Service;

use App\Constants\Constants;
use App\Service\HelperService;

/**
 * Class AdminService
 * @package Highlow\Service
 */
class ServerApiService
{
    /**
     * @var Constants
     */
    private $constants;
    /**
     * @var \App\Service\HelperService
     */
    private $helperService;

    /**
     * ServerApiService constructor.
     * @param Constants $constants
     * @param \App\Service\HelperService $helperService
     */
    public function __construct(Constants $constants, HelperService $helperService){
        $this->constants = $constants;
        $this->helperService = $helperService;
    }

    /**
     * @param $url
     * @param $header
     * @param int $is_post
     * @param string $post_data
     * @param string $customRequest
     * @return bool|string
     */
    public function curlCall($url, $header, $is_post = 0, $post_data = '', $customRequest = '') {
        $ch = curl_init();
        // dd($url);
        // $url = 'http://devons-docker-local.com/api/server_details';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if ($is_post == 1) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        if(!empty($customRequest)){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);

        $response = curl_exec($ch);//dd(curl_error($ch));
        if(curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);

        return $response;
    }

    /**
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function getServerDetails(){
        $filePath  = Constants::FILE_PATH.Constants::FILE_NAME;
        if(file_exists($filePath.'.xlsx')) {
            $this->helperService->convertXlsxToCsv($filePath);
            $filePath .= '.csv';
            if(!file_exists($filePath)){
                return [
                    "message" => "Error while creating csv file",
                    "data" => []
                ];
            }
            $handle = fopen($filePath, 'r');
            $csvHeaderFormat = Constants::CSV_HEADER_FORMAT;
            $result = $this->validateHeader($filePath, $csvHeaderFormat);
            if (!$result["flag"]) {
                return [
                    "message" => $result["message"],
                    "data" => []
                ];
            }
            $dataArray = $this->groupCsvData($handle);
            unlink($filePath);
            return [
                "message" => "Server details retrieved successfully",
                "data" => $dataArray
                ];
        } else {
            return [
                "message" => "File not found in Uploads folder",
                "data" => []
            ];
        }
    }

    /**
     * @param $handle
     * @return array
     */
    public function  groupCsvData($handle){
        $groupedList = [];
        fgetcsv($handle);
        while (($data = fgetcsv($handle)) != false) {
            $rowData = $this->getFormattedData($data);
            if(!empty($rowData['model'])){
                    $groupedList[] = $rowData;
            }

        }
        return $groupedList;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getFormattedData(array $data)
    {
        $transactionData = array();
        $transactionData['model'] = $data[0] ?? "";
        $transactionData['RAM'] = $data[1] ?? "";
        $transactionData['HDD'] = $data[2] ?? "";
        $transactionData['location'] = $data[3] ?? "";
        $transactionData['price'] = $data[4] ?? "";
        return $transactionData;
    }

    /**
     * @param $file
     * @param $csvHeaderFormat
     * @return array
     */
    public function validateHeader($file, $csvHeaderFormat){
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, 1000, ",");
        foreach ($csvHeaderFormat as $key => $value) {
            if (!in_array($value, $header)) {
                return [
                    "message" => "$value not found in CSV header",
                    "flag" => false
                ];
            }
        }
        return [
            "message" => "Valid CSV",
            "flag" => true
        ];

    }
}