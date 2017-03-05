<?php
namespace Oka\ApiBundle\EventListener;

use Oka\ApiBundle\Util\LoggerHelper;
use Oka\ApiBundle\Util\RequestHelper;
use Oka\ApiBundle\Util\ResponseHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
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
	
	public function __construct(RequestHelper $requestHelper, ResponseHelper $responseHelper, $host, $environment)
	{
		$this->requestHelper = $requestHelper;
		$this->responseHelper = $responseHelper;
		
		$this->host = $host;
		$this->environment = $environment;
		$this->stopWatch = new Stopwatch();
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
				$content = ResponseHelper::createError($statusCode, $exception->getMessage());
				
			} elseif ($exception instanceof InsufficientAuthenticationException) {
				$statusCode = 403;
				$content = ResponseHelper::createError($statusCode, $exception->getMessage());
				
			} elseif($exception instanceof BadRequestHttpException) { 
				$statusCode = $exception->getStatusCode();
				$content = ResponseHelper::createError($statusCode, $exception->getMessage());
				
			} elseif ($exception instanceof NotFoundHttpException) {
				$statusCode = $exception->getStatusCode();
				$content = ResponseHelper::createError($statusCode, sprintf('La ressource "%s" est introuvable ou n\'existe pas', $request->getRequestUri()));
				
			} elseif ($exception instanceof MethodNotAllowedHttpException) {
				$statusCode = $exception->getStatusCode();
				$content = ResponseHelper::createError($statusCode, $exception->getMessage());
				
			} elseif ($exception instanceof NotAcceptableHttpException) {
				$statusCode = $exception->getStatusCode();
				$content = ResponseHelper::createError($statusCode, $exception->getMessage());
				
			} elseif($exception instanceof AuthenticationException) {
				$statusCode = 403;
				$content = ResponseHelper::createError($statusCode, $exception->getMessage());
				
			} else {
				$statusCode = 500;
				$content = ResponseHelper::createError($statusCode, $exception instanceof HttpException ? $exception->getMessage() : ResponseHelper::SERVER_ERROR);
			}
			
			$this->logger->error(LoggerHelper::formatErrorMessage($exception));
			$event->setResponse($this->responseHelper->getAcceptableResponse($request, $content, $statusCode, [], $request->attributes->get('format', null), true));
		}
	}
	
	/**
	 * @param FinishRequestEvent $event
	 */
	public function onKernelFinishRequest(FinishRequestEvent $event) {}
	
	public static function getSubscribedEvents()
	{
		return [
				KernelEvents::REQUEST => 'onKernelRequest',
				KernelEvents::RESPONSE => 'onKernelResponse',
				KernelEvents::EXCEPTION => 'onKernelException',
// 				KernelEvents::FINISH_REQUEST => 'onKernelFinishRequest',
		];
	}
}