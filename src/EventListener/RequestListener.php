<?php
namespace Oka\ApiBundle\EventListener;

use Oka\ApiBundle\Http\HostRequestMatcher;
use Oka\ApiBundle\Service\ErrorResponseFactory;
use Oka\ApiBundle\Util\LoggerHelper;
use Oka\ApiBundle\Util\RequestUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
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
	 * @var TranslatorInterface $translator
	 */
	protected $translator;
	
	/**
	 * @var ErrorResponseFactory $errorFactory
	 */
	protected $errorFactory;
	
	/**
	 * @var string $environment
	 */
	protected $environment;
	
	/**
	 * @var Stopwatch $stopWatch
	 */
	protected $stopWatch;
	
	public function __construct(HostRequestMatcher $hostMatcher, TranslatorInterface $translator, ErrorResponseFactory $errorFactory, $environment)
	{
		$this->hostMatcher = $hostMatcher;
		$this->translator = $translator;
		$this->errorFactory = $errorFactory;
		$this->environment = $environment;
		$this->stopWatch = new Stopwatch();
	}
	
	/**
	 * @param GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();
		
		if (true === $event->isMasterRequest() && true === $this->hostMatcher->matches($request) && true === $request->query->has('debug')) {
			$this->stopWatch->start(self::STOP_WATCH_API_EVENT_NAME);
		}
	}
	
	/**
	 * @param FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event)
	{
		$request = $event->getRequest();
		$responseHeaders = $event->getResponse()->headers;
		
		// Utils Server
		$responseHeaders->set('X-Server-Time', date('c'));
		
		if (true === $event->isMasterRequest() && true === $this->hostMatcher->matches($request) && true === $request->query->has('debug')) {
			if (true === $this->stopWatch->isStarted(self::STOP_WATCH_API_EVENT_NAME)) {
				$event = $this->stopWatch->stop(self::STOP_WATCH_API_EVENT_NAME);
				$responseHeaders->set('X-Request-Duration', $event->getDuration() / 1000);
			}
		}
	}
	
	/**
	 * @param GetResponseForExceptionEvent $event
	 */
	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		if (false === $event->isMasterRequest() || $this->environment === 'dev') {
			return;
		}
		
		$request = $event->getRequest();
		
		if (true === $this->hostMatcher->matches($request)) {
			$exception = $event->getException();
			$format = $request->attributes->has('format') ? $request->attributes->get('format') : RequestUtil::getFirstAcceptableFormat($request, 'json');
			
			if ($exception instanceof UnauthorizedHttpException) {
				$response = $this->errorFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
			} elseif($exception instanceof AuthenticationException) {
				$response = $this->errorFactory->create($exception->getMessage(), 403, null, [], 403, [], $format);
			} elseif($exception instanceof BadRequestHttpException) {
				$response = $this->errorFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
			} elseif ($exception instanceof NotFoundHttpException) {
				$response = $this->errorFactory->create($this->translator->trans('response.not_found', ['%ressource%' => $request->getRequestUri()], 'OkaApiBundle'), 404, null, [], 404, [], $format);
			} elseif ($exception instanceof MethodNotAllowedHttpException) {
				$response = $this->errorFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
			} elseif ($exception instanceof NotAcceptableHttpException) {
				$response = $this->errorFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
			} else {
				if ($exception instanceof HttpException) {
					$response = $this->errorFactory->createFromException($exception, null, [], $exception->getStatusCode(), [], $format);
				} else {
					$response = $this->errorFactory->create($this->translator->trans('response.server_error', [], 'OkaApiBundle'), 500, null, [], 500, [], $format);
				}
			}
			
			$event->setResponse($response);
			$this->logErrException($exception);
		}
	}
	
	public static function getSubscribedEvents()
	{
		return [
				KernelEvents::REQUEST => 'onKernelRequest',
				KernelEvents::RESPONSE => 'onKernelResponse',
				KernelEvents::EXCEPTION => 'onKernelException'
		];
	}
}
