<?php
namespace Oka\ApiBundle\Util;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * 
 * @author cedrick
 *
 */
class ErrorResponseFactory
{
	/**
	 * Create new instance
	 * 
	 * @param string $message
	 * @param int $code
	 * @param string $property
	 * @param array $extras
	 * @param int $httpStatusCode
	 * @param array $httpHeaders
	 * @param string $format
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function create($message, $code = 500, $property = null, array $extras = [], $httpStatusCode = 500, $httpHeaders = [], $format = 'json')
	{
		$builder = ErrorResponseBuilder::Builder();
		
		return $builder->setError($message, $code, $property, $extras)
					   ->setHttpSatusCode($httpStatusCode)
					   ->setHttpHeaders($httpHeaders)
					   ->setFormat($format)
					   ->build();
	}
	
	/**
	 * Create new instance from ConstraintViolationList class
	 * 
	 * @param ConstraintViolationListInterface $errors
	 * @param string $message
	 * @param int $code
	 * @param string $property
	 * @param array $extras
	 * @param int $httpStatusCode
	 * @param array $httpHeaders
	 * @param string $format
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function createFromConstraintViolationList(ConstraintViolationListInterface $errors, $message = null, $code = 400, $property = null, array $extras = [], $httpStatusCode = 400, $httpHeaders = [], $format = 'json')
	{
		$builder = ErrorResponseBuilder::Builder();
		$builder->setError($message ?: 'Request not valid!', $code, $property, $extras)
				->setHttpSatusCode($httpStatusCode)
				->setHttpHeaders($httpHeaders)
				->setFormat($format);
		
		/** @var ConstraintViolationInterface $error */
		foreach ($errors as $error) {
			$builder->addChildError($error->getMessage(), $error->getCode(), $error->getPropertyPath(), ['invalidValue' => $error->getInvalidValue()]);
		}
		
		return $builder->build();
	}
	
	/**
	 * Create new instance from Exception class
	 * 
	 * @param \Exception $exception
	 * @param string $property
	 * @param array $extras
	 * @param int $httpStatusCode
	 * @param array $httpHeaders
	 * @param string $format
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function createFromException(\Exception $exception, $property = null, array $extras = [], $httpStatusCode = 500, $httpHeaders = [], $format = 'json')
	{
		$builder = ErrorResponseBuilder::Builder();
		
		return $builder->setError($exception->getMessage(), $exception->getCode() ?: 500, $property, $extras)
					   ->setHttpSatusCode($httpStatusCode)
					   ->setHttpHeaders($httpHeaders)
					   ->setFormat($format)
					   ->build();
	}
}