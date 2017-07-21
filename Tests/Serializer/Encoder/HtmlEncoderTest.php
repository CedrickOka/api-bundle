<?php
namespace Oka\ApiBundle\Tests\Serializer\Encoder;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Oka\ApiBundle\Serializer\Encoder\HtmlEncoder;

/**
 * 
 * @author cedrick
 * 
 */
class HtmlEncoderTest extends KernelTestCase
{
	public function testEncode()
	{
		$data = [
				'code' => 400, 
				'message' => 'Bad request.', 
				'errors' => [
						['code' => 400, 'message' => 'Bad request.', 'property' => 'username', 'extras' => ['invalidValue' => 'papa']],
						['code' => 400, 'message' => 'Bad request.', 'property' => 'username', 'extras' => ['invalidValue' => 'papa']]
				]
		];
		
		$encoder = new HtmlEncoder();
		$encodedHtml = $encoder->encode($data, 'html');
		
		$this->assertStringStartsWith('<html><body><ul>', $encodedHtml);
		$this->assertStringEndsWith('</ul></body></html>', $encodedHtml);
	}
}