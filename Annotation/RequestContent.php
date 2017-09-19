<?php
namespace Oka\ApiBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 * @Annotation
 * @Target("METHOD")
 */
final class RequestContent
{
	/**
	 * @Attribute(name="constraints", type="string", required=false)
	 * @var string $constraints
	 */
	protected $constraints;
	
	/**
	 * @Attribute(name="can_be_empty", type="boolean", required=false)
	 * @var boolean $canBeEmpty
	 */
	protected $canBeEmpty;
	
	/**
	 * @Attribute(name="enable_validation", type="boolean", required=false)
	 * @var boolean $enableValidation
	 */
	protected $enableValidation;
	
	/**
	 * @Attribute(name="validation_error_message", type="string", required=false)
	 * @var string $validationErrorMessage
	 */
	protected $validationErrorMessage;
	
	/**
	 * @Attribute(name="translation", type="array", required=false)
	 * @var array $translation
	 */
	protected $translation;
	
	/**
	 * @Attribute(name="validator_static_method", type="string", required=false)
	 * @var string $validatorStaticMethod
	 * @deprecated Use instead "constraints" property
	 */
	protected $validatorStaticMethod;
	
	/**
	 * @var string $translationDomain
	 */
	private $translationDomain;
	
	/**
	 * @var string $translationParameters
	 */
	private $translationParameters;
	
	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $data)
	{
		$this->canBeEmpty = isset($data['can_be_empty']) ? (boolean) $data['can_be_empty'] : false;
		$this->enableValidation = isset($data['enable_validation']) ? (boolean) $data['enable_validation'] : true;
		$this->constraints = isset($data['constraints']) ? $data['constraints'] : (isset($data['validator_static_method']) ? $data['validator_static_method'] : null);
		
		if ($this->constraints === null && $this->enableValidation === true) {
			throw new \InvalidArgumentException('You must define a "constraints" attribute for each @RequestContent annotation while request validation is enabled.');
		}
		
		$this->validationErrorMessage = isset($data['validation_error_message']) ? (string) $data['validation_error_message'] : 'response.bad_request';
		$this->translationDomain = isset($data['translation']['domain']) ? $data['translation']['domain'] : 'OkaApiBundle';
		$this->translationParameters = isset($data['translation']['parameters']) ? $data['translation']['parameters'] : [];
	}
	
	/**
	 * @return string
	 */
	public function getConstraints()
	{
		return $this->constraints;
	}
	
	/**
	 * @return boolean
	 */
	public function isCanBeEmpty()
	{
		return $this->canBeEmpty;
	}
	
	/**
	 * @return boolean
	 */
	public function isEnableValidation()
	{
		return $this->enableValidation;
	}
	
	/**
	 * @return string
	 */
	public function getValidationErrorMessage()
	{
		return $this->validationErrorMessage;
	}
	
	/**
	 * @return string
	 */
	public function getTranslationDomain()
	{
		return $this->translationDomain;
	}
	
	/**
	 * @return array
	 */
	public function getTranslationParameters()
	{
		return $this->translationParameters;
	}
	
	/**
	 * @deprecated Use instead RequestContent::getConstraints()
	 * @return string
	 */
	public function getValidatorStaticMethod()
	{
		return $this->validatorStaticMethod;
	}
}
