<?php
namespace Oka\ApiBundle\Tests\Util;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ErrorResponseFactoryTest extends KernelTestCase
{
	/**
	 * @var \Oka\ApiBundle\Service\ErrorResponseFactory $factory
	 */
	private $factory;
	
	public function setUp()
	{
		static::bootKernel();
		
		/** @var \Oka\ApiBundle\Service\ErrorResponseFactory $factory */
		$this->factory = static::$kernel->getContainer()->get('oka_api.error_response.factory');
	}
	
	public function testCreateFromException()
	{
		$exception = new NotFoundHttpException();
		$response = $this->factory->createFromException($exception);
		
		$this->assertEquals(404, $response->getStatusCode());
	}
}