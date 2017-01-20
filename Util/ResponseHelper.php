<?php
namespace Oka\ApiBundle\Util;

use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @author cedrick
 * 
 */
class ResponseHelper
{
	const BAD_REQUEST = 'Le format de la requête n\'est pas valide.';
	const SERVER_ERROR = 'Une erreur est survenue pendant le traitement de la requête.';
	const UNEXPECTED_ERROR = 'Une erreur inattendu est survenue le traitement de la requête.';
	const INVALID_CREDENTIALS = 'Le nom d\'utilisateur ou le mot de passe est incorrect.';
	
	const ERROR_BODY_ATTRIBUTE_NAME = 'error';
	const ERROR_CODE_ATTRIBUTE_NAME = 'code';
	const ERROR_MESSAGE_ATTRIBUTE_NAME = 'message';
	
	const EXTRAS = 'extras';
	const SUB_ERRORS = 'sub_errors';
	const PROPERTY_ERRORS = 'property_errors';
	
	/**
	 * @var Serializer $serializer
	 */
	protected $serializer;
	
	public function __construct(Serializer $serializer)
	{
		$this->serializer = $serializer;
	}
	
	/**
	 * Get reponse
	 * 
	 * @param Request $request
	 * @param mixed $content
	 * @param integer $statusCode
	 * @param array $headers
	 * @param string $format
	 * @param boolean $isError
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getAcceptableResponse(Request $request, $content, $statusCode, $headers = [], $format = null, $isError = false)
	{
		switch ($format ?: RequestHelper::getFirstAcceptableFormat($request)) {
			case 'html':
				if ($isError === true && isset($content[self::ERROR_BODY_ATTRIBUTE_NAME])) {
					$content = $content[self::ERROR_BODY_ATTRIBUTE_NAME][self::ERROR_MESSAGE_ATTRIBUTE_NAME];
				}
				
				$headers['Content-Type'] = 'text/html';
				break;
				
			case 'xml':
				$content = $this->serializer->serialize($content, 'xml');
				$headers['Content-Type'] = 'application/xml';
				break;
				
			default:
				$content = $this->serializer->serialize($content, 'json');
				$headers['Content-Type'] = 'application/json';
				break;
		}
		
		return new Response($content, $statusCode, $headers);
	}
	
	/**
	 * Create response error content
	 * 
	 * @param integer $code
	 * @param string $message
	 * @param mixed $propertyErrors
	 * @param array $subErrors
	 * @param array $extras
	 * @return string[]|string[][]
	 */
	public static function createError($code, $message, $propertyErrors = null, array $subErrors = [], array $extras = []) {
		$error = self::createSubError($code, $message);
		
		if (!empty($extras)) {
			$error[self::EXTRAS] = $extras;
		}
		
		$error = [self::ERROR_BODY_ATTRIBUTE_NAME => $error];
		
		if ($propertyErrors) {
			$error[self::PROPERTY_ERRORS] = $propertyErrors;
		}
		
		if (!empty($subErrors)) {
			$error[self::SUB_ERRORS] = $subErrors;
		}
		
		return $error;
	}
	
	/**
	 * Create sub error message for response
	 * 
	 * @param integer $code
	 * @param string $message
	 * @return string[]
	 */
	public static function createSubError($code, $message) {
		return [
				self::ERROR_CODE_ATTRIBUTE_NAME => $code,
				self::ERROR_MESSAGE_ATTRIBUTE_NAME => $message
		];
	}
	
	/**
	 * Format Error message for response
	 * 
	 * @param integer $code
	 * @param string $message
	 * @param mixed $propertyErrors
	 * @param array $subErrors
	 * @param array $extras
	 * @return string[]|string[][]
	 * @deprecated
	 */
	public static function buildErrorMessage($code, $message, $propertyErrors = null, array $subErrors = [], array $extras = [])
	{
		return self::createError($code, $message, $propertyErrors, $subErrors, $extras);
	}
	
	/**
	 * Format Sub error message for response
	 * 
	 * @param integer $code
	 * @param string $message
	 * @return string[]
	 * @deprecated
	 */
	public static function buildSubErrorsMessage($code, $message)
	{
		return self::createSubError($code, $message);
	}
	
	public static function stringCast($value)
	{
		if (is_array($value)) {
			$cast = '';
			
			foreach ($value as $content) {
				$cast .= is_array($content) ? self::stringCast($value) : $content;
			}
			
			$value = $cast;
		}
		
		return (string) $value;
	}
}