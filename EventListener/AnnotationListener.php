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
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
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
	 * @var TranslatorInterface $translator
	 */
	protected $translator;
	
	/**
	 * @var ErrorResponseFactory $errorFactory
	 */
	protected $errorFactory;
	
	/**
	 * @param Reader $reader
	 * @param ValidatorInterface $validator
	 * @param TranslatorInterface $translator
	 * @param ErrorResponseFactory $errorFactory
	 */
	public function __construct(Reader $reader, ValidatorInterface $validator, TranslatorInterface $translator, ErrorResponseFactory $errorFactory)
	{
		$this->reader = $reader;
		$this->validator = $validator;
		$this->translator = $translator;
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
					$response = $this->errorFactory->create($this->translator->trans('response.not_acceptable_api_version', ['%version%' => $version], 'OkaApiBundle'), 406, null, [], 406, [], $format);					
				} elseif (strtolower($protocol) !== $annotation->getProtocol()) {
					$response = $this->errorFactory->create($this->translator->trans('response.not_acceptable_protocol', ['%version%' => $protocol], 'OkaApiBundle'), 406, null, [], 406, [], $format);
				} elseif (!$request->attributes->has('format')) {
					$response = $this->errorFactory->create($this->translator->trans('response.not_acceptable_format', ['%formats%' => implode(', ', $request->getAcceptableContentTypes())], 'OkaApiBundle'), 406, null, [], 406, [], $format);
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
				$validationHasFailed = false;
				// Retrieve query paramters in URI or request content
				$requestContent = $request->isMethod('GET') ? $request->query->all() : RequestUtil::getContentLikeArray($request);
				
				if (true === $annotation->isEnableValidation()) {
					if (!empty($requestContent)) {
						$constraints = $annotation->getConstraints();
						$reflectionMethod = new \ReflectionMethod($controller[0], $constraints);
						
						if (false === $reflectionMethod->isStatic()) {
							throw new \InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: Constraints method "%s" is not static.', RequestContent::getName(), $constraints));
						}
						
						if ($reflectionMethod->getNumberOfParameters() > 0) {
							throw new \InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: Constraints method "%s" must not have of arguments.', RequestContent::getName(), $constraints));
						}
						
						$reflectionMethod->setAccessible(true);
						$errors = $this->validator->validate($requestContent, $reflectionMethod->invoke(null));
						$validationHasFailed = $errors->count() > 0;
					} else {
						$validationHasFailed = !$annotation->isCanBeEmpty();
					}
				}
				
				if ($validationHasFailed === true) {
					$event->setController(function(Request $request) use ($errors) {
						$format = $request->attributes->has('format') ? $request->attributes->get('format') : RequestUtil::getFirstAcceptableFormat($request) ?: 'json';
						return $this->errorFactory->createFromConstraintViolationList($errors, $this->translator->trans('response.bad_request', [], 'OkaApiBundle'), 400, null, [], 400, [], $format);
					});
				} else {
					$request->attributes->set('requestContent', $requestContent);
				}
				return;
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