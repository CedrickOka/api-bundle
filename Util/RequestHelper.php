<?php
namespace Oka\ApiBundle\Util;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
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
	
// 	/**
// 	 * @var Translator $translator
// 	 */
// 	protected $translator;
	
	public function __construct(ValidatorInterface $validator)//, Translator $translator)
	{
		$this->validator = $validator;
// 		$this->translator = $translator;
	}
	
	/**
	 * Chekc if request content is valid
	 * 
	 * @param mixed $value
	 * @param Constraint $constraints
	 * @param array $groups
	 * @return string[]
	 */
	public function isValid($value, Constraint $constraints, array $groups = null)
	{
		$errors = [];
		
		foreach ($this->validator->validate($value, $constraints, $groups) as $error) {
			$errors[preg_replace('#\[(.+)\]#', '$1', $error->getPropertyPath())] = $error->getMessage();
// 			$errors[preg_replace('#\[(.+)\]#', '$1', $error->getPropertyPath())] = $this->translator->trans($error->getMessage());
		}
		
		return $errors;
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
	 * Parse request query
	 * 
	 * @param Request $request
	 * @param string $key
	 * @param string $delimiter
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public static function parseQueryString(Request $request, $key, $delimiter = null, $defaultValue = null)
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
}