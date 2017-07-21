<?php
namespace Oka\ApiBundle\EventListener;

use Oka\ApiBundle\Http\HostRequestMatcher;
use Oka\ApiBundle\Util\ErrorResponseFactory;
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
	const STOP_WATCH_API_EVENT_NAME = 'oka_api.request_duration';
	
	/**
	 * @var HostRequestMatcher $hostMatcher
	 */
	protected $hostMatcher;
	
	/**
	 * @var string $environment
	 */
	protected $environment;
	
	/**
	 * @var Stopwatch $stopWatch
	 */
	protected $stopWatch;
	
	public function __construct(HostRequestMatcher $hostMatcher, $environment)
	{
		$this->hostMatcher = $hostMatcher;
		$this->environment = $environment;
		$this->stopWatch = new Stopwatch();
	}
	
	/**
	 * @param GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();
		
		if ($event->isMasterRequest() && $this->hostMatcher->matches($request) && $request->query->get('debug', false)) {
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
		
		if ($event->isMasterRequest() && $this->hostMatcher->matches($request) && $request->query->get('debug', false)) {
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
		
		if ($this->hostMatcher->matches($request)) {
			$exception = $event->getException();
			$format = $request->attributes->has('format') ? $request->attributes->get('format') : RequestHelper::getFirstAcceptableFormat($request) ?: 'json';
			
			if ($exception instanceof UnauthorizedHttpException) {
				$response = ErrorResponseFactory::createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
			} elseif ($exception instanceof InsufficientAuthenticationException) {
				$response = ErrorResponseFactory::createFromException($exception, null, [], 403, [], $format);
			} elseif($exception instanceof BadRequestHttpException) {
				$response = ErrorResponseFactory::createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
			} elseif ($exception instanceof NotFoundHttpException) {
				$response = ErrorResponseFactory::create(sprintf('La ressource "%s" est introuvable ou n\'existe pas.', $request->getRequestUri()), 404, null, [], 404, [], $format);
			} elseif ($exception instanceof MethodNotAllowedHttpException) {
				$response = ErrorResponseFactory::createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
			} elseif ($exception instanceof NotAcceptableHttpException) {
				$response = ErrorResponseFactory::createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
			} elseif($exception instanceof AuthenticationException) {
				$response = ErrorResponseFactory::create($exception->getMessage(), 403, null, [], 403, [], $format);
			} else {
				if ($exception instanceof HttpException) {
					$response = ErrorResponseFactory::createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
				} else {
					$response = ErrorResponseFactory::create(ResponseHelper::SERVER_ERROR, 500, null, [], 500, [], $format);
				}
			}
			
			$event->setResponse($response);
			$this->logErrException($exception);
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