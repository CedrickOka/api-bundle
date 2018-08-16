<?php
namespace Oka\ApiBundle\Tests\Controller;

use Oka\ApiBundle\Util\WsseUtil;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * To run the test a user wsse must be configured with the following credentials
 * username = admin
 * password = admin
 * allowedIps = [127.0.0.1]
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class AuthorizationControllerTest extends WebTestCase
{
	public function testIpAllowed()
	{
		/** @var \Symfony\Component\BrowserKit\Client $client */
		$client = static::createClient();
		$container = $client->getContainer();
		
		$client->request('GET', $container->get('router')->generate('oka_api_allowed_ips_test', [], UrlGenerator::ABSOLUTE_URL), [], [], [
				'HTTP_X-WSSE' 	=> WsseUtil::generateToken('admin', 'admin'),
				'REMOTE_ADDR' 	=> '127.0.0.1'
		]);
		$response = $client->getResponse();
		
		$this->assertEquals(200, $response->getStatusCode());
	}
	
	public function testIpNotAllowed()
	{
		/** @var \Symfony\Component\BrowserKit\Client $client */
		$client = static::createClient();
		$container = $client->getContainer();
		
		$client->request('GET', $container->get('router')->generate('oka_api_not_allowed_ips_test', [], UrlGenerator::ABSOLUTE_URL), [], [], [
				'HTTP_X-WSSE' 	=> WsseUtil::generateToken('admin', 'admin'),
// 				'HTTP_HOST' 	=> $container->getParameter('web_host.api'),
				'REMOTE_ADDR' 	=> '192.168.100.1'
		]);
		$response = $client->getResponse();
		
		$this->assertEquals(403, $response->getStatusCode());
	}
}
