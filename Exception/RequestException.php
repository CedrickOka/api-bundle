<?php
namespace Oka\ApiBundle\Exception;

/**
 * 
 * @author cedrick
 * 
 */
class RequestException extends \Exception
{
	public function __construct($title, $message, $code, $previous) {
		parent::__construct($message, $code, $previous);
	}
}