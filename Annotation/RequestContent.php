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
	 * @Attribute(name="constraints", required=false, type="string")
	 * @var string $constraints
	 */
	protected $constraints;
	
	/**
	 * @Attribute(name="can_be_empty", required=false, type="boolean")
	 * @var boolean $canBeEmpty
	 */
	protected $canBeEmpty;
	
	/**
	 * @Attribute(name="enable_validation", required=false, type="boolean")
	 * @var boolean $enableValidation
	 */
	protected $enableValidation;
	
	/**
	 * @Attribute(name="validator_static_method", required=false, type="string")
	 * @var string $validatorStaticMethod
	 * @deprecated Use instead "constraints" property
	 */
	protected $validatorStaticMethod;
	
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
	 * @deprecated Use instead RequestContent::getConstraints()
	 * @return string
	 */
	public function getValidatorStaticMethod()
	{
		return $this->validatorStaticMethod;
	}
}