<?php
namespace Oka\ApiBundle\Util;

use Oka\ApiBundle\Serializer\Encoder\HtmlEncoder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ErrorResponseBuilder implements ErrorResponseBuilderInterface
{
	/**
	 * @var array $error
	 */
	protected $error;
	
	/**
	 * @var array $childErrors
	 */
	protected $childErrors;
	
	/**
	 * @var int $httpStatusCode
	 */
	protected $httpStatusCode;
	
	/**
	 * @var array $httpHeaders
	 */
	protected $httpHeaders;
	
	/**
	 * @var string $format
	 */
	protected $format;
	
	/**
	 * Constructor.
	 */
	protected function __construct()
	{
		$this->childErrors = [];
		$this->httpStatusCode = 500;
		$this->httpHeaders = [];
		$this->format = 'json';
	}
	
	/**
	 * Create Error array object
	 * 
	 * @param string $message
	 * @param int $code
	 * @param string $property
	 * @param array $extras
	 * @return array
	 */
	protected function createError($message, $code, $property = null, array $extras = [])
	{
		if (!is_string($message)) {
			throw new \UnexpectedValueException(sprintf('The Error message must be a string or object implementing __toString(), "%s" given.', gettype($message)));
		}
		
		if (!is_int($code)) {
			throw new \UnexpectedValueException(sprintf('The Error code must be a integer, "%s" given.', gettype($code)));
		}
		
		$item = ['message' => (string) $message, 'code' => (int) $code];
		
		if ($property !== null) {
			if (!is_string($property)) {
				throw new \UnexpectedValueException(sprintf('The Error property must be a string or object implementing __toString(), "%s" given.', gettype($property)));
			}
			
			$item['property'] = (string) $property;
		}
		
		if (!empty($extras)) {
			$item['extras'] = $extras;
		}
		
		return $item;
	}
	
	/**
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilder
	 */
	public static function getInstance()
	{
		return new ErrorResponseBuilder();
	}
	
	/**
	 * @deprecated Since version 1.6.3 use instead ErrorResponseBuilderInterface::getInstance() method.
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilder
	 */
	public static function Builder()
	{
		return self::getInstance();
	}
	
	/**
	 * Set Error
	 * 
	 * @param string $message
	 * @param int $code
	 * @param string $property
	 * @param array $extras
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilder
	 */
	public function setError($message, $code, $property = null, array $extras = [])
	{
		$this->error = $this->createError($message, $code, $property, $extras);
		return $this;
	}
	
	/**
	 * Add child Error
	 * 
	 * @param string $message
	 * @param int $code
	 * @param string $property
	 * @param array $extras
	 * @return \Oka\ApiBundle\Util\ErrorResponse
	 */
	public function addChildError($message, $code, $property = null, array $extras = [])
	{
		$this->childErrors[] = $this->createError($message, $code, $property, $extras);		
		return $this;
	}
	
	/**
	 * @param int $httpStatusCode
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilder
	 */
	public function setHttpSatusCode($httpStatusCode = 500)
	{
		$this->httpStatusCode = $httpStatusCode;
		return $this;
	}
	
	/**
	 * @param array $httpHeaders
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilder
	 */
	public function setHttpHeaders(array $httpHeaders = [])
	{
		$this->httpHeaders = $httpHeaders;
		return $this;
	}
	
	/**
	 * @param string $format
	 * @return \Oka\ApiBundle\Util\ErrorResponseBuilder
	 */
	public function setFormat($format)
	{
		if (!in_array($format, self::DEFAULT_FORMATS)) {
			throw new \UnexpectedValueException(sprintf('The format must be a value between "%s", "%s" given.', implode(', ', self::DEFAULT_FORMATS), $format));
		}
		
		$this->format = $format;
		return $this;
	}
	
	/**
	 * @throws \LogicException
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function build()
	{
		if (!is_array($this->error)) {
			throw new \LogicException('ErrorResponseBuilder::$error property must be defined.');
		}
		
		$data = ['error' => $this->error];
		
		if (!empty($this->childErrors)) {
			$data['errors'] = $this->childErrors;
		}
		
		if ($this->format === 'html' || $this->format === 'xml' ) {
			$serializer = new Serializer([new ObjectNormalizer()], [new HtmlEncoder(), new XmlEncoder()]);
			$response = new Response($serializer->encode($data, $this->format), $this->httpStatusCode, $this->httpHeaders);
		} else {
			$response = new JsonResponse($data, $this->httpStatusCode, $this->httpHeaders);
		}
		
		return $response;
	}
}
