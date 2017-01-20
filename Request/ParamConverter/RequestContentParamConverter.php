<?php
namespace Oka\ApiBundle\Request\ParamConverter;

use Oka\ApiBundle\Util\RequestHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * 
 * @author cedrick
 * 
 */
class RequestContentParamConverter implements ParamConverterInterface
{
	/**
	 * @var RequestHelper $requestHelper
	 */
	protected $requestHelper;
	
	public function __construct(RequestHelper $requestHelper)
	{
		$this->requestHelper = $requestHelper;
	}
	
	/**
	 * {@inheritdoc}
	 *
	 * @see \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface::apply()
	 */
	public function apply(Request $request, ParamConverter $configuration)
	{
		$options = $this->getOptions();
		
		$errorsValidation = [];
		$requestContent = RequestHelper::getContentLikeArray($request);
		
		if ($options['validator_constraint_class'] !== null) {
			if (is_subclass_of($options['validator_constraint_class'], 'Symfony\Component\Validator\Constraint')) {
				$errorsValidation = $this->requestHelper->isValid($requestContent, new $options['validator_constraint_class']($options['validator_constraint_options'] ?: []));
			}
		}
		
		if ((!$requestContent && false === $options['can_be_empty'] && false === $configuration->isOptional()) || $errorsValidation) {
			throw new BadRequestHttpException('The request body is empty or malformed.');
		}
		
		$request->attributes->set('requestContent', $requestContent);
		
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 *
	 * @see \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface::supports()
	 */
	public function supports(ParamConverter $configuration)
	{
		if (null !== $configuration->getClass()) {
			return false;
		}
		
		return 'requestContent' === $configuration->getName();
	}
	
	public function getOptions()
	{
		$defaultsValue = [
				'validator_constraint_class' => null,
				'validator_constraint_options' => null,
				'can_be_empty' => false
		];
		
		$passedOptions = $configuration->getOptions();
		
		if ($extraKeys = array_diff(array_keys($passedOptions), array_keys($defaultValues))) {
			throw new \InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: %s', $this->getAnnotationName($configuration), implode(', ', $extraKeys)));
		}
		
		return array_replace($defaultValues, $passedOptions);
	}
	
	private function getAnnotationName(ParamConverter $configuration)
	{
		$r = new \ReflectionClass($configuration);
		
		return $r->getShortName();
	}
}