<?php
namespace Oka\ApiBundle\Encoder;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface ResponseContentEncoderInterface
{
	/**
	 * Check if encoder supports encoding
	 * 
	 * @param string $encoding
	 * @return boolean true if encoder supports encoding, false otherwise
	 */
	public function supports($encoding);
	
	/**
	 * Encode
	 * 
	 * @param string $encoding
	 * @param string $content
	 * @return string
	 */
	public function encode($encoding, $content);
}
