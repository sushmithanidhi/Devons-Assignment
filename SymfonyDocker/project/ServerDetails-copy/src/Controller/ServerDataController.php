<?php

namespace App\Controller;
use App\Constants\Constants;
use Goldco\Constants\Admin\HolidayStates;
use Goldco\Document\Mailer\EmailLog;
use Highlow\Constants\BatchConstants;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\HelperService;
use App\Service\ServerApiService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\String\Slugger\SluggerInterface;
 class ServerDataController extends AbstractController
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger = null){
        $this->logger = $logger;
    }
    public function index(){
        return $this->render('layout.html.twig');
    }
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request,HelperService $helperService,ServerApiService $apiService,LoggerInterface $logger)
    {

        $page = 1;
        $per_page_record = 25;
        if(isset($_GET["page"])){
            $page = $_GET["page"];
        }
        $start_from = ($page-1) * $per_page_record;
        $selectedFilter = [];
        try {
            if(count($request->query->all()) > 1){
                $selectedFilter['selectedRam']  = $request->query->get('ramFilter');
                $selectedFilter['selectedhdd'] = $request->query->get('hddFilter');
                $selectedFilter['selectedLocation']  = $request->query->get('locationFilter');
            }
            $serverDetails = '';
            $routePath = $this->generateUrl('api_server_details');
            $url = 'http://' . $_SERVER['HTTP_HOST'] . $routePath;
            $header = array('Content-Type:application/json');
            //data will be stored in cache for the after first api cal and cleared when the new file is being uploaded
            // check if the the data is there in cache. If yes get data from cache otherwise make an api call
            $response = json_decode($apiService->curlCall($url, $header), true);
            if(!$response){
                $response = $this->getServerDetails($request,$apiService,$logger,true);
            }
            if ($response['code'] == 200) {
                $serverDetails = json_decode($response["data"], true)["data"];
            } else {
                $message = $response["data"]["data"] ?? "No data found. Please upload file ";
                $this->addFlash("error", $message);
            }
            $filteredData = [];
            if(!empty($selectedFilter['selectedRam']) || !empty($selectedFilter['selectedhdd']) || !empty($selectedFilter['selectedLocation'])) {
                foreach ($serverDetails as $details) {
                    //dump( strpos($details["location"], $selectedFilter['selectedLocation']) !== false);
//                    if(!empty($selectedFilter['selectedRam']) &&){
//
//                    }
//                    if((!empty($selectedFilter['selectedRam']) && (strpos($details["RAM"], $selectedFilter['selectedRam']) !== false || strpos($details["RAM"], $selectedFilter['selectedRam']) === 0)) &&
//                        (!empty($selectedFilter['selectedhdd']) && (strpos($details["HDD"], $selectedFilter['selectedhdd']) !== false || strpos($details["HDD"], $selectedFilter['selectedhdd']) === 0)) &&
//                        (!empty($selectedFilter['selectedLocation']) && (strpos($details["location"], $selectedFilter['selectedLocation']) !== false || strpos($details["location"], $selectedFilter['selectedLocation']) === 0))
//                    ){
//
//                            array_push($filteredData,$details);
//
//                    }

                    if(!empty($selectedFilter['selectedRam'])) {
                        if (!(strpos($details["RAM"], $selectedFilter['selectedRam']) !== false || strpos($details["RAM"], $selectedFilter['selectedRam']) === 0)) {;
                            continue;
                        }
                    }

                    if(!empty($selectedFilter['selectedhdd'])) {
                        if (!(strpos($details["HDD"], $selectedFilter['selectedhdd']) !== false || strpos($details["HDD"], $selectedFilter['selectedhdd']) === 0)) {
                            continue;
                        }
                    }

                    if(!empty($selectedFilter['selectedLocation'])) {
                        if (!(strpos($details["location"], $selectedFilter['selectedLocation']) !== false || strpos($details["location"], $selectedFilter['selectedLocation']) === 0)) {
                            continue;
                        }
                    }
                    array_push($filteredData,$details);
//                    dd($details);
//                    if(!empty($selectedFilter['selectedhdd'])){
//                        if(strpos($details["HDD"], $selectedFilter['selectedhdd']) !== false) {
//                            array_push($filteredData,$details);
//                        }
//                    }
//                    if(!empty($selectedFilter['selectedLocation'])){
//                        if(strpos($details["location"], $selectedFilter['selectedLocation']) !== false) {
//                            array_push($filteredData,$details);
//                        }
//                    }
                }//die;
                if(empty($filteredData)){
                    $this->addFlash("error", "No data found");
                }
                $serverDetails = !empty($filteredData) ?  $filteredData : '';
            }
//dd($serverDetails);

            $ramFilter = Constants::FILTER_RAM;
            $HHDType = Constants::HDD_FORM;
            $location = Constants::Location;

            $dataCount =  $serverDetails ? count($serverDetails) : 0;
            $rs_result = $serverDetails ?  array_slice($serverDetails,$start_from,$per_page_record) : '' ;

            $total_records =  $serverDetails ? count($serverDetails) : 0;
            $total_pages = $serverDetails ? ceil($total_records / $per_page_record) : 0;
            $pagLink = "";
            $serverDetails  = $rs_result ?? $serverDetails ;
            $serverDetails ? $serverDetails = $helperService->getUniqueId($serverDetails) : '';
            $limit = 10;//dump($total_pages);
            $startLimit = floor($page / $limit) == 0 ? 1 : (floor($page / $limit) * $limit) + 1;//dd($total_pages);
            if($total_pages < $limit){
                $limit = $total_pages;
            }
            if($page == $total_pages) {
                $startLimit = ($total_pages - $limit)+1;
            }
            $lastLimit = $startLimit + ($limit-1);
            $lastLimit > $total_pages ? $lastLimit = $total_pages : '';
            // dump($startLimit);dump($lastLimit);die;
            return $this->render(
                'list.html.twig', ['startLimit'=> $startLimit,'lastLimit' => $lastLimit,'dataCount' => $dataCount,'selectedFilter' => $selectedFilter,'pgLink' => $page,'page' => $page,'pagLink' => $pagLink,'total_pages' => $total_pages,'serverDetails' => $serverDetails, 'types' => $ramFilter, 'hardDrive' => $HHDType, 'location' => $location]);
        }catch (\Exception $e) {
            $this->addFlash("error", "Something went wrong!!!!!  Please check the logs");
            $this->logger->error(HelperService::exceptionFormatter($e));
            //log
        }
            return $this->render(
                'list.html.twig',['error' => true]);

    }

    /**
     * @param Request $request
     * @param ServerApiService $apiService
     * @return JsonResponse
     */
    public function getServerDetails(Request $request, ServerApiService $apiService,$manualCall = false){
        try {
            $serverDetails = $apiService->getServerDetails();
            if (!empty($serverDetails["data"])) {
                $result = ['code' => Response::HTTP_OK,  'message' => $serverDetails["message"], 'data' => json_encode($serverDetails)];
            } else {
                $result = ['code' => Response::HTTP_NOT_FOUND,  'message' => $serverDetails["message"]];
            }

        } catch (\Exception $e) {
            $result = ['code' => Response::HTTP_BAD_REQUEST,  'message' => HelperService::exceptionFormatter($e)];
            $this->logger->error(HelperService::exceptionFormatter($e));
        }
        if(!$manualCall) {
            return new JsonResponse($result);
         }else { 
             return $result;
            }
    }

    public function getSelectedServerDetails(Request $request,ServerApiService $apiService,HelperService $helperService){
        try {
            $serverDetails = $apiService->getServerDetails();
            if (!empty($serverDetails["data"])) {
                $result = ['code' => Response::HTTP_OK,  'message' => $serverDetails["message"], 'data' => json_encode($serverDetails)];
            } else {
                $result = ['code' => Response::HTTP_NOT_FOUND,  'message' => $serverDetails["message"]];
            }
            $selectedRowIds = explode(',',$_POST['selectedIds']);//dump($selectedRowIds);
            $selectedData = [];$count = 0;
            $serverDetails = $helperService->getUniqueId($serverDetails["data"]);//dd($serverDetails[0]);
            $uniqueData = array_column($serverDetails,'uniqueId');
            foreach ($selectedRowIds as $selectedRowId){//dump($selectedRowId);
                $keyData = array_search($selectedRowId,$uniqueData);//dd($keyData);
                $keyData !== null ? array_push($selectedData,$serverDetails[$keyData]) : '';
//                foreach ($serverDetails as $serverData) {$count++;
//                    if($selectedRowId == $serverData['uniqueId']){
//                        array_push($selectedData,$serverData);
//                        break;
//                    }
//                }
            }//dd($selectedData);
            return $this->render(
                'compareList.html.twig',['serverDetails' => $selectedData]);
        } catch (\Exception $e) {dd($e->getMessage());
            $result = ['code' => Response::HTTP_BAD_REQUEST,  'message' => HelperService::exceptionFormatter($e)];
            $this->logger->error(HelperService::exceptionFormatter($e));
        }
        return new JsonResponse($result);
    }


    /**
     * @param Request $request
     * @param HelperService $helperService
     * @param ServerApiService $apiService
     * @param SluggerInterface $slugger
     * @return Response|void
     */
    public function uploadAction(Request $request, HelperService $helperService, ServerApiService $apiService){
        try {
            $constraints = [new File(['maxSize' => '2048k',
                'mimeTypes' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/xlsx'],
                'mimeTypesMessage' => 'Please upload Xslx file only.',
                'uploadIniSizeErrorMessage' => 'Please upload file size below 2MB',])];
            $form = $this->createFormBuilder()
                ->add('upload', FileType::class, ['required' => true, 'label' => 'Upload Xslx file', 'constraints' => $constraints,
                    'attr' => [
                        'data-validation-engine' => 'validate[required]',
                        'class' => 'form-control',
                    ],])
                ->getForm();

            if ('POST' == $request->getMethod()) {
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $formData = $form->getData();
                    $file = $formData['upload'];
                    $filePath = Constants::FILE_PATH;
                    if (!file_exists($filePath)) {
                        mkdir($filePath, 0777, true);
                    }
                    $filePath = $filePath . Constants::FILE_NAME;
                    $uploadedFile = $filePath . '.xlsx';
                    move_uploaded_file($file->getPathName(), $uploadedFile);
                    //store css in local and add it in cache
                    $helperService->convertXlsxToCsv($filePath);
                    $filePath .= '.csv';
                    if (!file_exists($filePath)) {
                        $this->addFlash("success", "File has been added successfully.");
                        return;
                    }
                    $csvHeaderFormat = Constants::CSV_HEADER_FORMAT;
                    $result = $apiService->validateHeader($filePath, $csvHeaderFormat);
                    if (!$result["flag"]) {
                        $this->addFlash("error", $result["message"]);
                        unlink($filePath);
                        unlink($uploadedFile);
                    } else {
                        $this->addFlash("success", "File has been added successfully.");
                        unlink($filePath);
                    }
                }
            }
        }catch(\Exception $e){
            $this->logger->error(HelperService::exceptionFormatter($e));
            $this->addFlash("error", "Something went wrong!!!!!  Please check the logs");
        }
        return $this->render('UploadFile.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

