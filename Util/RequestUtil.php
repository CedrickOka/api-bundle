<?php
namespace Oka\ApiBundle\Util;

use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
final class RequestUtil
{
	/**
	 * Parse request query
	 * 
	 * @param Request $request
	 * @param string $key
	 * @param string $delimiter
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public static function parseQueryStringToArray(Request $request, $key, $delimiter = null, $defaultValue = null)
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