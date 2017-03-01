<?php
namespace Oka\ApiBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Oka\ApiBundle\Annotation\AccessControl;
use Oka\ApiBundle\Annotation\RequestContent;
use Oka\ApiBundle\Util\LoggerHelper;
use Oka\ApiBundle\Util\RequestHelper;
use Oka\ApiBundle\Util\ResponseHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * 
 * @author cedrick
 * 
 */
class RequestListener extends LoggerHelper implements EventSubscriberInterface
{
	/**
	 * @var Reader $reader
	 */
	protected $reader;
	
	/**
	 * @var RequestHelper $requestHelper
	 */
	protected $requestHelper;
	
	/**
	 * @var ResponseHelper $responseHelper
	 */
	protected $responseHelper;
	
	/**
	 * @var string $host
	 */
	protected $host;
	
	/**
	 * @var string $environment
	 */
	protected $environment;
	
	/**
	 * @var Stopwatch $stopWatch
	 */
	protected $stopWatch;
	
	const STOP_WATCH_API_EVENT_NAME = 'oka_api.request_duration';
	
	public function __construct(Reader $reader, RequestHelper $requestHelper, ResponseHelper $responseHelper, $host, $environment)
	{
		$this->reader = $reader;
		$this->requestHelper = $requestHelper;
		$this->responseHelper = $responseHelper;
		
		$this->host = $host;
		$this->environment = $environment;
		$this->stopWatch = new Stopwatch();
	}
	
	public static function getSubscribedEvents()
	{
		return [
				KernelEvents::REQUEST => 'onKernelRequest',
				KernelEvents::CONTROLLER => [['onAccessControlAnnotation', 3], ['onRequestContentAnnotation', 2]],
				KernelEvents::RESPONSE => 'onKernelResponse',
				KernelEvents::EXCEPTION => 'onKernelException',
// 				KernelEvents::FINISH_REQUEST => 'onKernelFinishRequest',
		];
	}
	
	/**
	 * @param GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();
		
		if ($event->isMasterRequest() && $request->getHost() === $this->host && $request->query->get('debug', false)) {
			$this->stopWatch->start(self::STOP_WATCH_API_EVENT_NAME);
		}
	}
	
	/**
	 * @param FilterControllerEvent $event
	 */
	public function onAccessControlAnnotation(FilterControllerEvent $event)
	{
		if (!$event->isMasterRequest() || !is_array($controller = $event->getController())) {
			return;
		}
		
		$method = new \ReflectionMethod($controller[0], $controller[1]);
		
		if (!$annotations = $this->reader->getMethodAnnotations($method)) {
			return;
		}
		
		$request = $event->getRequest();
		
		foreach ($annotations as $annotation) {
			if ($annotation instanceof AccessControl) {
				$responseContent = null;
				$version = $request->attributes->get('version');
				$protocol = $request->attributes->get('protocol');
				$acceptablesContentTypes = $request->getAcceptableContentTypes();
				
				// Configure acceptable content type of response
				if (empty($acceptablesContentTypes) || in_array('*/*', $acceptablesContentTypes, true)) {
					$request->attributes->set('format', $annotation->getFormats()[0]);
				} else {
					foreach ($acceptablesContentTypes as $contentType) {
						$format = $request->getFormat($contentType);
						
						if (in_array($format, $annotation->getFormats(), true)) {
							$request->attributes->set('format', $format);
							break;
						}
					}
				}
				
				if (!version_compare($version, $annotation->getVersion(), $annotation->getVersionOperator())) {
					$responseContent = ResponseHelper::createError(406, sprintf('The request does not support the API version number "%s".', $version));
				} elseif (strtolower($protocol) !== $annotation->getProtocol()) {
					$responseContent = ResponseHelper::createError(406, sprintf('The request does not support the protocol "%s".', $protocol));	
				} elseif (!$request->attributes->has('format')) {
					$responseContent = ResponseHelper::createError(406, sprintf('Unsupported response format with request accept: "%s".', implode(', ', $request->getAcceptableContentTypes())));
				}
				
				if ($responseContent !== null) {
					$event->stopPropagation();
					$event->setController(function(Request $request) use ($responseContent) {
						return $this->responseHelper->getAcceptableResponse($request, $responseContent, 406, [], null, true);
					});
				}
				break;
			}
		}
	}
	
	/**
	 * @param FilterControllerEvent $event
	 */
	public function onRequestContentAnnotation(FilterControllerEvent $event)
	{
		if (!$event->isMasterRequest() || !is_array($controller = $event->getController())) {
			return;
		}
		
		$method = new \ReflectionMethod($controller[0], $controller[1]);
		
		if (!$annotations = $this->reader->getMethodAnnotations($method)) {
			return;
		}
		
		$request = $event->getRequest();
		
		foreach ($annotations as $annotation) {
			if ($annotation instanceof RequestContent) {
				$errorsValidation = [];
				$requestContent = RequestHelper::getContentLikeArray($request);
				
				if ($methodName = $annotation->getValidatorStaticMethod()) {
					$reflectionMethod = new \ReflectionMethod($controller[0], $methodName);
					
					if (false === $reflectionMethod->isStatic()) {
						throw new \InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: Validator method "%s" is not static.', RequestContent::getName(), $annotation->getValidatorStaticMethod()));
					}
					if ($reflectionMethod->getNumberOfParameters() > 0) {
						throw new \InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: Validator method "%s" must not have of arguments.', RequestContent::getName(), $annotation->getValidatorStaticMethod()));
					}
					
					$reflectionMethod->setAccessible(true);
					$errorsValidation = $this->requestHelper->isValid($requestContent, $reflectionMethod->invoke(null));
				}
				
				if ((!$requestContent && false === $annotation->isCanBeEmpty()) || !empty($errorsValidation)) {
					$event->setController(function(Request $request) use ($errorsValidation) {
						return $this->responseHelper->getAcceptableResponse(
								$request, 
								ResponseHelper::createError(400, 'The request body is empty or malformed.', $errorsValidation), 
								400, 
								null,
								$request->attributes->get('format', null), true);
					});
				} else {
					$request->attributes->set('requestContent', $requestContent);
				}
				break;
			}
		}
	}
		
	/**
	 * @param FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event)
	{
		$request = $event->getRequest();
		$requestHeaders = $request->headers;
		$responseHeaders = $event->getResponse()->headers;
		
		// Utils Server
		$responseHeaders->set('X-Server-Time', date('c'));
		
		if ($event->isMasterRequest() && $request->getHost() === $this->host && $request->query->get('debug', false)) {
			if ($this->stopWatch->isStarted(self::STOP_WATCH_API_EVENT_NAME)) {
				$event = $this->stopWatch->stop(self::STOP_WATCH_API_EVENT_NAME);
				$responseHeaders->set('X-Request-Duration', $event->getDuration() / 1000);
			}
		}
		
		if ($requestHeaders->has('x-wsse')) {
			$responseHeaders->set('X-Secure-With', 'WSSE');
		} elseif ($requestHeaders->has('Authorization')) {
			$responseHeaders->set('X-Secure-With', 'JWT');
		}
	}
	
	/**
	 * @param GetResponseForExceptionEvent $event
	 */
	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		if (!$event->isMasterRequest() || $this->environment === 'dev') {
			return;
		}
		
		$request = $event->getRequest();
		
		if ($request->getHost() === $this->host) {
			$exception = $event->getException();
			
			if ($exception instanceof UnauthorizedHttpException) {
				$statusCode = $exception->getStatusCode();
				$content = ResponseHelper::buildErrorMessage($statusCode, $exception->getMessage());
				
			} elseif ($exception instanceof InsufficientAuthenticationException) {
				$statusCode = 403;
				$content = ResponseHelper::buildErrorMessage($statusCode, $exception->getMessage());
				
			} elseif($exception instanceof BadRequestHttpException) { 
				$statusCode = $exception->getStatusCode();
				$content = ResponseHelper::buildErrorMessage($statusCode, $exception->getMessage());
				
			} elseif ($exception instanceof NotFoundHttpException) {
				$statusCode = $exception->getStatusCode();
				$content = ResponseHelper::buildErrorMessage($statusCode, sprintf('La ressource "%s" est introuvable ou n\'existe pas', $request->getRequestUri()));
				
			} elseif ($exception instanceof MethodNotAllowedHttpException) {
				$statusCode = $exception->getStatusCode();
				$content = ResponseHelper::buildErrorMessage($statusCode, $exception->getMessage());
				
			} elseif ($exception instanceof NotAcceptableHttpException) {
				$statusCode = $exception->getStatusCode();
				$content = ResponseHelper::buildErrorMessage($statusCode, $exception->getMessage());
				
			} elseif($exception instanceof AuthenticationException) {
				$statusCode = 403;
				$content = ResponseHelper::buildErrorMessage($statusCode, $exception->getMessage());
				
			} else {
				$statusCode = 500;
				$content = ResponseHelper::buildErrorMessage($statusCode, $exception instanceof HttpException ? $exception->getMessage() : ResponseHelper::SERVER_ERROR);
			}
			
			$this->logger->error(LoggerHelper::formatErrorMessage($exception));
			$event->setResponse($this->responseHelper->getAcceptableResponse($request, $content, $statusCode, [], $request->attributes->get('format', null), true));
		}
	}
	
	/**
	 * @param FinishRequestEvent $event
	 */
	public function onKernelFinishRequest(FinishRequestEvent $event) {}
}