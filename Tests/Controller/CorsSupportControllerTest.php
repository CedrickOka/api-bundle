<?php
namespace Oka\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class CorsSupportControllerTest extends WebTestCase
{
	/**
	 * CORS pattern configuration for test
	 * 
	 * cors:
	 *     pattern: ^.*\/test/cors$
	 */
	public function testIndexCorsSupportPattern()
	{
		/** @var \Symfony\Bundle\FrameworkBundle\Client $client */
		$client = static::createClient();
		$container = $client->getContainer();
		$uri = $container->get('router')->generate('oka_api_cors_support_test', [], UrlGenerator::ABSOLUTE_URL);
		
		$client->request('OPTIONS', $uri, [], [], [
				'HTTP_Origin' => $uri,
				'HTTP_Access-Control-Request-Method' => 'GET',
				'HTTP_Access-Control-Request-Headers' => 'X-Test'
		]);
		$response = $client->getResponse();
		
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertTrue($response->headers->contains('Access-Control-Allow-Methods', 'GET'));
		$this->assertTrue($response->headers->contains('Access-Control-Allow-Headers', 'X-Test'));
	}
	
	/**
	 * CORS pattern configuration for test
	 *
	 * cors:
	 *     pattern: ^.*\/test/cors$
	 */
	public function testIndexCorsNotSupportPattern()
	{
		/** @var \Symfony\Bundle\FrameworkBundle\Client $client */
		$client = static::createClient();
		$container = $client->getContainer();
		$uri = $container->get('router')->generate('oka_api_cors_support_test_not_support', [], UrlGenerator::ABSOLUTE_URL);
		
		$client->request('OPTIONS', $uri, [], [], [
				'HTTP_Origin' => $uri,
				'HTTP_Access-Control-Request-Method' => 'GET',
				'HTTP_Access-Control-Request-Headers' => 'X-Test'
		]);
		$response = $client->getResponse();
		
		$this->assertEquals(405, $response->getStatusCode());
		$this->assertFalse($response->headers->contains('Access-Control-Allow-Methods', 'GET'));
		$this->assertFalse($response->headers->contains('Access-Control-Allow-Headers', 'X-Test'));
	}
}
