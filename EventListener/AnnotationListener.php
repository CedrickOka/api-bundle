<?php
namespace Oka\ApiBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Oka\ApiBundle\Annotation\AccessControl;
use Oka\ApiBundle\Annotation\RequestContent;
use Oka\ApiBundle\Service\ErrorResponseFactory;
use Oka\ApiBundle\Service\LoggerHelper;
use Oka\ApiBundle\Util\RequestUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
	 * @var ValidatorInterface $validator
	 */
	protected $validator;
	
	/**
	 * @var ErrorResponseFactory $errorFactory
	 */
	protected $errorFactory;
	
	public function __construct(Reader $reader, ValidatorInterface $validator, ErrorResponseFactory $errorFactory)
	{
		$this->reader = $reader;
		$this->validator = $validator;
		$this->errorFactory = $errorFactory;
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
				
				$response = null;
				$version = $request->attributes->get('version');
				$protocol = $request->attributes->get('protocol');
				$format = RequestUtil::getFirstAcceptableFormat($request) ?: 'json';
				
				if (!version_compare($version, $annotation->getVersion(), $annotation->getVersionOperator())) {
					$response = $this->errorFactory->create(sprintf('The request does not support the API version number "%s".', $version), 406, null, [], 406, [], $format);					
				} elseif (strtolower($protocol) !== $annotation->getProtocol()) {
					$response = $this->errorFactory->create(sprintf('The request does not support the protocol "%s".', $protocol), 406, null, [], 406, [], $format);
				} elseif (!$request->attributes->has('format')) {
					$response = $this->errorFactory->create(sprintf('Unsupported response format with request accept: "%s".', implode(', ', $request->getAcceptableContentTypes())), 406, null, [], 406, [], $format);
				}
				
				if ($response !== null) {
					$event->stopPropagation();
					$event->setController(function(Request $request) use ($response) {
						return $response;
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
				$requestContent = RequestUtil::getContentLikeArray($request);
				
				if ($methodName = $annotation->getValidatorStaticMethod()) {
					$reflectionMethod = new \ReflectionMethod($controller[0], $methodName);
					
					if (false === $reflectionMethod->isStatic()) {
						throw new \InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: Validator method "%s" is not static.', RequestContent::getName(), $annotation->getValidatorStaticMethod()));
					}
					
					if ($reflectionMethod->getNumberOfParameters() > 0) {
						throw new \InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: Validator method "%s" must not have of arguments.', RequestContent::getName(), $annotation->getValidatorStaticMethod()));
					}
					
					$reflectionMethod->setAccessible(true);
					$errors = $this->validator->validate($requestContent, $reflectionMethod->invoke(null));
				}
				
				if ((!$requestContent && false === $annotation->isCanBeEmpty()) || (isset($errors) && $errors->count() > 0)) {
					$event->setController(function(Request $request) use ($errors) {
						$format = $request->attributes->has('format') ? $request->attributes->get('format') : RequestUtil::getFirstAcceptableFormat($request) ?: 'json';
						return $this->errorFactory->createFromConstraintViolationList($errors, 'The request body is not valid or malformed.', 400, null, [], 400, [], $format);
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
				KernelEvents::CONTROLLER => [
						['onAccessControlAnnotation', 3], 
						['onRequestContentAnnotation', 2]
				]
		];
	}
}