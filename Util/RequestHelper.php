<?php
namespace Oka\ApiBundle\Util;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * 
 * @author cedrick
 * 
 */
class RequestHelper
{
	/**
	 * @var ValidatorInterface $validator
	 */
	protected $validator;
	
	public function __construct(ValidatorInterface $validator)
	{
		$this->validator = $validator;
	}
	
	/**
	 * Chekc if request content is valid
	 * 
	 * @param mixed $value
	 * @param Constraint $constraints
	 * @param array $groups
	 * @return string[]
	 * 
	 * @deprecated
	 */
	public function isValid($value, Constraint $constraints, array $groups = null)
	{
		$errors = [];
		
		/** @var ConstraintViolationInterface $error */
		foreach ($this->validator->validate($value, $constraints, $groups) as $error) {
			$errors[preg_replace('#\[(.+)\]#', '$1', $error->getPropertyPath())] = $error->getMessage();
		}
		
		return $errors;
	}
	
	/**
	 * Parse request query
	 *
	 * @param Request $request
	 * @param string $key
	 * @param string $delimiter
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function parseQueryStringToArray(Request $request, $key, $delimiter = null, $defaultValue = null)
	{
		$value = $request->query->get($key, $defaultValue);
		
		if ($value && $delimiter !== null) {
			$value = array_map(function($value){
				return self::sanitizeQueryString($value);
			}, explode($delimiter, $value));
		}
		
		return $value;
	}
	
	/**
	 * Sanitize request query
	 * 
	 * @param string $query
	 * @return string
	 */
	public static function sanitizeQueryString($query)
	{
		return trim(rawurldecode($query));
	}
	
	/**
	 * Convert request content in array
	 * 
	 * @param Request $request
	 * @return array
	 */
	public static function getContentLikeArray(Request $request)
	{
		switch ($request->getContentType()) {
			case 'json':
				return json_decode($request->getContent(), true);
			case 'form':
				return $request->request->all();
			default;
				return [];
		}
	}
	
	/**
	 * Get first acceptable response format
	 * 
	 * @param Request $request
	 * @return string|NULL
	 */
	public static function getFirstAcceptableFormat(Request $request)
	{
		$acceptableContentTypes = $request->getAcceptableContentTypes();
		
		return $request->getFormat(empty($acceptableContentTypes) ? 'text/html' : $acceptableContentTypes[0]);
	}
}