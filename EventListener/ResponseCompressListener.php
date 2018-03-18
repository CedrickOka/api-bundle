<?php
namespace Oka\ApiBundle\EventListener;

use Oka\ApiBundle\Encoder\ResponseContentEncoderInterface;
use Oka\ApiBundle\Http\HostRequestMatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ResponseCompressListener implements EventSubscriberInterface
{
	/**
	 * @var HostRequestMatcher $hostMatcher
	 */
	protected $hostMatcher;
	
	/**
	 * @var ResponseContentEncoderInterface $encoder
	 */
	protected $encoder;
	
	public function __construct(HostRequestMatcher $hostMatcher, ResponseContentEncoderInterface $encoder)
	{
		$this->hostMatcher = $hostMatcher;
		$this->encoder = $encoder;
	}
	
	/**
	 * @param FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event)
	{
		$request = $event->getRequest();
		
		if (true === $event->isMasterRequest() && true === $this->hostMatcher->matches($request)) {
			$response = $event->getResponse();
			
			if ($response instanceof StreamedResponse) {
				return;
			}
			
			if ($encodings = preg_split('#(\s*),(\s*)#', mb_strtolower($request->headers->get('Accept-Encoding', '')))) {
				$content = $response->getContent();
				
				if (true === is_string($content)) {
					foreach ($encodings as $encoding) {
						if (true === $this->encoder->supports($encoding)) {
							$response->setContent($this->encoder->encode($encoding, $content));
							$response->headers->set('Content-Encoding', $encoding);
							$response->headers->set('Content-Length', mb_strlen($response->getContent()));
							break;
						}
					}
				}
			}
		}
	}
	
	public static function getSubscribedEvents()
	{
		return [
				KernelEvents::RESPONSE => 'onKernelResponse'
		];
	}
}
