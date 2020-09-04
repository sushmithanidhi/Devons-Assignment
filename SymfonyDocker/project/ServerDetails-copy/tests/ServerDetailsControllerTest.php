<?php 
namespace App\Tests;

use App\Service\ServerApiService;
use App\Constants\Constants;
use App\Service\HelperService;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Controller\ServerDataController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VerifServerDetailsControllerTest  extends WebTestCase
{
    public function testGetServerDetails()
    {
        $client = static::createClient();

        $client->request('GET', '/api/server_details');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());    
    }

    public function testGetServerPageTitle()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'upload-files');

        $this->assertSame('ServerCheckList', $crawler->filter('title')->text());    
    }
}
?>