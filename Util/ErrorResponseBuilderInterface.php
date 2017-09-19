<?php
namespace Oka\ApiBundle\Util;

use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @author cedrick
 * 
 */
interface ErrorResponseBuilderInterface
{
	const DEFAULT_FORMATS = ['html', 'json', 'xml'];
	
	/**
	 * @deprecated Since version 1.6.3 use instead ErrorResponseBuilderInterface::getInstance() method.
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilderInterface
	 */
	public static function Builder();
	
	/**
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilderInterface
	 */
	public static function getInstance();
	
	/**
	 * Set Error
	 * 
	 * @param string $message
	 * @param int $code
	 * @param string $property
	 * @param array $extras
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilderInterface
	 */
	public function setError($message, $code, $property = null, array $extras = []);
	
	/**
	 * Add child Error
	 * 
	 * @param string $message
	 * @param int $code
	 * @param string $property
	 * @param array $extras
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilderInterface
	 */
	public function addChildError($message, $code, $property = null, array $extras = []);
	
	/**
	 * @param int $httpStatusCode
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilderInterface
	 */
	public function setHttpSatusCode($httpStatusCode = 500);
	
	/**
	 * @param array $httpHeaders
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilderInterface
	 */
	public function setHttpHeaders(array $httpHeaders = []);
	
	/**
	 * @param string $format
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilderInterface
	 */
	public function setFormat($format);
	
	/**
	 * @throws \LogicException
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function build();
}
