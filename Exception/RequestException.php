<?php
namespace Oka\ApiBundle\Exception;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class RequestException extends \Exception
{
	public function __construct($title, $message, $code, $previous) {
		parent::__construct($message, $code, $previous);
	}
}