<?php
namespace Oka\ApiBundle\Encoder;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class ResponseContentEncoder implements ResponseContentEncoderInterface
{
	public function encode($encoding, $content) {
		switch (true) {
			case $encoding === 'gzip':
				$content = gzencode($content, 9);
				break;
		
			case $encoding === 'deflate':
				$content = gzdeflate($content, 9);
				break;
		}
		
		return $content;
	}
	
	public function supports($encoding) {
		return in_array($encoding, ['gzip', 'deflate'], true);
	}
}
