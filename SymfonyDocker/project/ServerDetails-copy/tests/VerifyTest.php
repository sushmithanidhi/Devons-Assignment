<?php 
namespace App\Tests;

use App\Util\Calculator;
use PHPUnit\Framework\TestCase;
use App\Service\ServerApiService;
use App\Constants\Constants;
use App\Service\HelperService;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Controller\ServerDataController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class VerifyTest extends TestCase
{
    /** @var AEON */
    private $serverApiObj = null;

    public function setUp(){
        $constants = new Constants();
        $this->helperService = new  HelperService();
        $this->serverDataController = new ServerDataController();
        $this->serverApiObj = new ServerApiService($constants,$this->helperService);
    }
    public function testGetServerDetails()
    {
        $serverDetails = $this->serverApiObj->getServerDetails();
        $this->assertTrue(is_array($serverDetails),'Expected response should be array');
    }

    public function testGetSelectedServerDetails(){
        $request = new Request;
        $serverDetails = $this->serverDataController->getServerDetails($request,$this->serverApiObj);
        $expected = JsonResponse::class;
        $this->assertInstanceOf($expected,$serverDetails,'Expected response should be json response object');
    }
}
?>