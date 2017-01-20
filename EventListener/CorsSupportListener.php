<?php
namespace Oka\ApiBundle\EventListener;

use Oka\ApiBundle\Util\LoggerHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * 
 * @author cedrick
 * 
 */
class CorsSupportListener extends LoggerHelper implements EventSubscriberInterface
{
	/**
	 * @var string $host
	 */
	protected $host;
	
	/**
	 * @var array $allowedOrigins
	 */
	protected $allowedOrigins;
	
	/**
	 * @var array $exposeHeaders
	 */
	protected $exposeHeaders;
	
	public function __construct($host, array $allowedOrigins = [], array $exposeHeaders = [])
	{
		$this->host = $host;
		$this->allowedOrigins = $allowedOrigins;
		$this->exposeHeaders = $exposeHeaders;
	}
	
	public static function getSubscribedEvents()
	{
		return [
				KernelEvents::RESPONSE => 'onKernelResponse'
		];
	}
	
	/**
	 * @param FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event)
	{
		if (!$event->isMasterRequest() || $event->getRequest()->getHost() !== $this->host) {
			return;
		}
		
		$responseHeaders = $event->getResponse()->headers;
		
		if (!empty($this->allowedOrigins)) {
			$responseHeaders->set('Access-Control-Allow-Origin', implode('|', $this->allowedOrigins));
		}
		
		$exposeHeaders = ['Cache-Control, Content-Encoding X-Server-Time'];
		$responseHeaders->set('Access-Control-Expose-Headers', implode(', ', array_merge(
				$exposeHeaders, 
				$this->exposeHeaders, 
				$responseHeaders->has('Access-Control-Expose-Headers') ? preg_split('#,#', $responseHeaders->get('Access-Control-Expose-Headers', '')) : []
		)));
	}
}