<?php
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * 
 * @author cedrick
 * 
 */
class CorsSupportControllerTest extends WebTestCase
{
	public function testIndex()
	{
		/** @var \Symfony\Bundle\FrameworkBundle\Client $client */
		$client = static::createClient();
		$uri = sprintf('http://%s/app_dev.php/cors/support', $client->getContainer()->getParameter('oka_api.http_host'));
		
		$crawler = $client->request('OPTIONS', $uri, [], [], [
				'HTTP_Access-Control-Request-Method' => 'GET',
				'HTTP_Access-Control-Request-Headers' => 'X-Test'
		]);
		
		$response = $client->getResponse();		
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertTrue($response->headers->contains('Access-Control-Allow-Methods', 'GET'));
		$this->assertTrue($response->headers->contains('Access-Control-Allow-Headers', 'X-Test'));
	}
}