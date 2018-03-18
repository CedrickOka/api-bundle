<?php
namespace Oka\ApiBundle\EventListener;

use Oka\ApiBundle\CorsOptions;
use Oka\ApiBundle\Util\LoggerHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class CorsSupportListener extends LoggerHelper implements EventSubscriberInterface
{
	/**
	 * @var array $parameters
	 */
	protected $parameters;
	
	public function __construct(array $parameters = [])
	{
		$this->parameters = $parameters;
	}
	
	/**
	 * Allow CORS Request Support
	 * 
	 * @param GetResponseForExceptionEvent $event
	 */
	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		$request = $event->getRequest();
		$exception = $event->getException();
		
		if ($exception instanceof MethodNotAllowedHttpException && true === $request->isMethod('OPTIONS') && true === $request->headers->has('Origin')) {			
			foreach ($this->parameters as $cors) {
				if (true === $this->match($request, $cors[CorsOptions::HOST], $cors[CorsOptions::PATTERN])) {
					$response = $this->apply($request, new Response(), $cors);
					$event->setResponse($response);
					break;
				}
			}			
		}
	}
	
	public static function getSubscribedEvents()
	{
		return [
				KernelEvents::EXCEPTION => ['onKernelException', 255],
		];
	}
	
	/**
	 * @param Request $request
	 * @param string $host
	 * @param string $pattern
	 * @return boolean
	 */
	private function match(Request $request, $host, $pattern)
	{
		if (isset($host) && $request->getHost() !== $host) {
			return false;
		}
		
		if (isset($pattern) && !preg_match(sprintf('#%s#', strtr($pattern, '#', '\#')), $request->getPathInfo())) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $cors
	 * @return Response
	 */
	private function apply(Request $request, Response $response, array $cors = [])
	{
		// Define CORS allow_origin
		$response->headers->set('Access-Control-Allow-Origin', !empty($cors[CorsOptions::ALLOW_ORIGIN]) ? implode('|', $cors[CorsOptions::ALLOW_ORIGIN]) : '*');
		
		// Define CORS allow_methods
		if (!empty($cors[CorsOptions::ALLOW_METHODS])) {
			$response->headers->set('Access-Control-Allow-Methods', implode(',', $cors[CorsOptions::ALLOW_METHODS]));
		} elseif ($request->headers->has('Access-Control-Request-Method')) {
			$response->headers->set('Access-Control-Allow-Methods', $request->headers->get('Access-Control-Request-Method'));
		}
		
		// Define CORS allow_headers
		if (!empty($cors[CorsOptions::ALLOW_HEADERS])) {
			$response->headers->set('Access-Control-Allow-Headers', implode(',', $cors[CorsOptions::ALLOW_HEADERS]));
		} elseif ($request->headers->has('Access-Control-Request-Headers')) {
			$response->headers->set('Access-Control-Allow-Headers', $request->headers->get('Access-Control-Request-Headers'));
		}
		
		// Define CORS allow_credentials
		if (true === $cors[CorsOptions::ALLOW_CREDENTIALS]) {
			$response->headers->set('Access-Control-Allow-Credentials', 'true');
		}
		
		// Define CORS expose_headers
		$exposeHeaders = array_merge(['Cache-Control, Content-Type, Content-Length, Content-Encoding, X-Server-Time, X-Request-Duration, X-Secure-With'], $cors[CorsOptions::EXPOSE_HEADERS]);
		$response->headers->set('Access-Control-Expose-Headers', implode(',', array_unique($exposeHeaders, SORT_REGULAR)));
		
		// Define CORS max_age
		if ($cors[CorsOptions::MAX_AGE] > 0) {
			$response->headers->set('Access-Control-Max-Age', $cors[CorsOptions::MAX_AGE]);
		}
		
		// Overwrite exception status code
		$response->headers->set('X-Status-Code', 200);
		
		return $response;
	}
}
