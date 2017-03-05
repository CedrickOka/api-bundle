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
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * 
 * @author cedrick
 * 
 */
class AnnotationListener extends LoggerHelper implements EventSubscriberInterface
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
	
	public function __construct(Reader $reader, RequestHelper $requestHelper, ResponseHelper $responseHelper)
	{
		$this->reader = $reader;
		$this->requestHelper = $requestHelper;
		$this->responseHelper = $responseHelper;
	}
	
	/**
	 * @param FilterControllerEvent $event
	 */
	public function onAccessControlAnnotation(FilterControllerEvent $event)
	{
		if (!$event->isMasterRequest() || !is_array($controller = $event->getController())) {
			return;
		}
		
		if (!$annotations = $this->reader->getMethodAnnotations(new \ReflectionMethod($controller[0], $controller[1]))) {
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
		
		if (!$annotations = $this->reader->getMethodAnnotations(new \ReflectionMethod($controller[0], $controller[1]))) {
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
	
	public static function getSubscribedEvents()
	{
		return [
				KernelEvents::CONTROLLER => [['onAccessControlAnnotation', 3], ['onRequestContentAnnotation', 2]]
		];
	}
}