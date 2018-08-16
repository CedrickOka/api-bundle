<?php
namespace Oka\ApiBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Oka\ApiBundle\Service\ErrorResponseFactory;
use Oka\ApiBundle\Util\LoggerHelper;
use Oka\ApiBundle\Util\RequestUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
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
	public function onController(FilterControllerEvent $event)
	{
		if (false === $event->isMasterRequest() || false === is_array($controller = $event->getController())) {
			return;
		}
		
		$listeners = ['onAccessControlAnnotation', 'onRequestContentAnnotation'];
		$reflMethod = new \ReflectionMethod($controller[0], $controller[1]);
		
		foreach ($listeners as $listener) {
			$this->$listener($event, $reflMethod);
			
			if (true === $event->isPropagationStopped()) {
				return;
			}
		}
	}
	
	/**
	 * @param FilterControllerEvent $event
	 * @param \ReflectionMethod $reflMethod
	 */
	private function onAccessControlAnnotation(FilterControllerEvent $event, \ReflectionMethod $reflMethod)
	{
		/** @var \Oka\ApiBundle\Annotation\AccessControl $annotation */
		if (!$annotation = $this->reader->getMethodAnnotation($reflMethod, 'Oka\ApiBundle\Annotation\AccessControl')) {
			return;
		}
		
		$request = $event->getRequest();
		$acceptablesContentTypes = $request->getAcceptableContentTypes();
		
		if (false === empty($acceptablesContentTypes)) {
			foreach ($acceptablesContentTypes as $contentType) {
				$format = $request->getFormat($contentType);
				
				if (true === in_array($format, $annotation->getFormats(), true)) {
					$request->attributes->set('format', $format);
					break;
				}
			}
			
			if (false === $request->attributes->has('format') && true === in_array('*/*', $acceptablesContentTypes, true)) {
				$request->attributes->set('format', $annotation->getFormats()[0]);
			}
		} else {
			$request->attributes->set('format', $annotation->getFormats()[0]);
		}
		
		$response = null;
		$version = $request->attributes->get('version');
		$protocol = $request->attributes->get('protocol');
		$format = RequestUtil::getFirstAcceptableFormat($request, $annotation->getFormats()[0]);
		
		switch (false) {
			case version_compare($version, $annotation->getVersion(), $annotation->getVersionOperator()):
				$response = $this->errorFactory->create($this->translator->trans('response.not_acceptable_api_version', ['%version%' => $version], 'OkaApiBundle'), 406, null, [], 406, [], $format);
				break;
			case strtolower($protocol) === $annotation->getProtocol():
				$response = $this->errorFactory->create($this->translator->trans('response.not_acceptable_protocol', ['%version%' => $protocol], 'OkaApiBundle'), 406, null, [], 406, [], $format);
				break;
			case $request->attributes->has('format'):
				$response = $this->errorFactory->create($this->translator->trans('response.not_acceptable_format', ['%formats%' => implode(', ', $acceptablesContentTypes)], 'OkaApiBundle'), 406, null, [], 406, [], $format);
				break;
		}
		
		if (null === $response) {
			$version = $request->attributes->set('versionNumber', $annotation->getVersionNumber());
		} else {
			$event->stopPropagation();
			$event->setController(function() use ($response) {
				return $response;
			});
		}
	}
	
	/**
	 * @param FilterControllerEvent $event
	 * @param \ReflectionMethod $reflMethod
	 */
	private function onRequestContentAnnotation(FilterControllerEvent $event, \ReflectionMethod $reflMethod)
	{
		/** @var \Oka\ApiBundle\Annotation\RequestContent $annotation */
		if (!$annotation = $this->reader->getMethodAnnotation($reflMethod, 'Oka\ApiBundle\Annotation\RequestContent')) {
			return;
		}
		
		$errors = null;
		$validationHasFailed = false;
		$request = $event->getRequest();
		$controller = $event->getController();
		
		// Retrieve query paramters in URI or request content
		$requestContent = $request->isMethod('GET') ? $request->query->all() : RequestUtil::getContentLikeArray($request);
		
		if (true === $annotation->isEnableValidation()) {
			if (false === empty($requestContent)) {
				$constraints = $annotation->getConstraints();
				$reflectionMethod = new \ReflectionMethod($controller[0], $constraints);
				
				if (false === $reflectionMethod->isStatic()) {
					throw new \InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: Constraints method "%s" is not static.', get_class($annotation), $constraints));
				}
				
				if ($reflectionMethod->getNumberOfParameters() > 0) {
					throw new \InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: Constraints method "%s" must not have of arguments.', get_class($annotation), $constraints));
				}
				
				$reflectionMethod->setAccessible(true);
				$errors = $this->validator->validate($requestContent, $reflectionMethod->invoke(null));
				$validationHasFailed = $errors->count() > 0;
			} else {
				$validationHasFailed = !$annotation->isCanBeEmpty();
			}
		}
		
		if (false === $validationHasFailed) {
			$request->attributes->set('requestContent', $requestContent);
		} else {
			$event->setController(function(Request $request) use ($annotation, $errors) {
				$message = $this->translator->trans($annotation->getValidationErrorMessage(), $annotation->getTranslationParameters(), $annotation->getTranslationDomain());
				$format = $request->attributes->has('format') ? $request->attributes->get('format') : RequestUtil::getFirstAcceptableFormat($request, 'json');
				
				if (null === $errors) {
					$response = $this->errorFactory->create($message, 400, null, [], 400, [], $format);
				} else {
					$response = $this->errorFactory->createFromConstraintViolationList($errors, $message, 400, null, [], 400, [], $format);
				}
				
				return $response;
			});
		}
	}
	
	public static function getSubscribedEvents()
	{
		return [
				KernelEvents::CONTROLLER => 'onController'
		];
	}
}
