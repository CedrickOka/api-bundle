<?php
namespace Oka\ApiBundle\Util;

use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @author cedrick
 * 
 * @deprecated  Use instead `ErrorResponseBuilder` class or ErrorResponseFactory class.
 */
class ResponseHelper
{
	const BAD_REQUEST = 'Le format de la requête n\'est pas valide.';
	const SERVER_ERROR = 'Une erreur est survenue pendant le traitement de la requête.';
	const UNEXPECTED_ERROR = 'Une erreur inattendu est survenue le traitement de la requête.';
	const INVALID_CREDENTIALS = 'Le nom d\'utilisateur ou le mot de passe est incorrect.';
	const ERROR_EXTRAS = 'extras';
	const ERROR_COLLECTION = 'errors';
	const ERROR_PROPERTIES = 'properties';
	
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
	 * 
	 * @deprecated  Use instead `ErrorResponseBuilder` class or ErrorResponseFactory class. 
	 */
	public function getAcceptableResponse(Request $request, $content, $statusCode, $headers = [], $format = null, $isError = false)
	{
		@trigger_error('The '.__METHOD__.' method is deprecated since version 1.0.1 and will be removed in 2. Use instead `ErrorResponseBuilder` class or ErrorResponseFactory class.', E_USER_DEPRECATED);
		
		switch ($format ?: RequestHelper::getFirstAcceptableFormat($request)) {
			case 'html':
				if ($isError === true && isset($content['error'])) {
					$content = $content['error']['message'];
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
	 * @param array  $properties
	 * @param array $errors
	 * @param array $extras
	 * @return string[]|string[][]
	 * 
	 * @deprecated since version 1.0.1, to be removed in 2. Use instead `ErrorResponseBuilder` class or ErrorResponseFactory class.
	 */
	public static function createError($code, $message, array $properties = [], array $extras = [], array $errors = [])
	{
		@trigger_error('The '.__METHOD__.' method is deprecated since version 1.0.1 and will be removed in 2. Use instead `ErrorResponseBuilder` class or ErrorResponseFactory class.', E_USER_DEPRECATED);
		
		$error = ['error' => ['code' => $code, 'message' => $message]];
		
		if (!empty($errors)) {
			$error['errors'] = $errors;
		}
		
		if (!empty($extras)) {
			$error['error']['extras'] = $extras;
		}
		
		if (!empty($properties)) {
			$error['error']['properties'] = $properties;
		}
		
		return $error;
	}
	
	public static function stringCast($value)
	{
		if (is_array($value)) {
			$cast = '';
			foreach ($value as $content) {
				$cast .= is_array($content) ? self::stringCast($value) : ' '.$content;
			}
			$value = $cast;
		}
		
		return (string) $value;
	}
}