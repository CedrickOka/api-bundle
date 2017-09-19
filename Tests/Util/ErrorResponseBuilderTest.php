<?php
namespace Oka\ApiBundle\Tests\Util;

use Oka\ApiBundle\Util\ErrorResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ErrorResponseBuilderTest extends KernelTestCase
{
	public function testBuildHtmlResponse()
	{
		$builder = ErrorResponseBuilder::getInstance();
		/** @var \Symfony\Component\HttpFoundation\Response $response */
		$response = $builder->setFormat('html')
							->setError('Bad request.', 400)
							->addChildError('Bad request.', 400, 'username', ['invalidValue' => 'papa'])
							->addChildError('Bad request.', 400, 'username', ['invalidValue' => 'papa'])
							->addChildError('Bad request.', 400, 'username', ['invalidValue' => 'papa'])
							->build();
		$content = $response->getContent();
		
		$this->assertStringStartsWith('<html><body><ul>', $content);
		$this->assertStringEndsWith('</ul></body></html>', $content);
	}
	
	public function testBuildJsonResponse()
	{
		$builder = ErrorResponseBuilder::getInstance();
		/** @var \Symfony\Component\HttpFoundation\Response $response */
		$response = $builder->setError('Bad request.', 400)
							->addChildError('Bad request.', 400, 'username', ['invalidValue' => 'papa'])
							->addChildError('Bad request.', 400, 'username', ['invalidValue' => 'papa'])
							->addChildError('Bad request.', 400, 'username', ['invalidValue' => 'papa'])
							->build();
		$content = $response->getContent();
		
		$this->assertEquals('application/json', $response->headers->get('Content-Type'));
		$this->assertStringStartsWith('{', $content);
		$this->assertStringEndsWith('}', $content);
	}
}
