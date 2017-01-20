<?php
namespace Oka\ApiBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("METHOD")
 * 
 * @author Cedrick O. Baidai <cedrickoka@fermentuse.com>
 */
final class RequestContent
{
	/**
	 * @Attribute(name="validator_static_method", required=true, type="string")
	 * @var string $validatorStaticMethod
	 */
	protected $validatorStaticMethod;
	
	/**
	 * @Attribute(name="can_be_empty", required=false, type="boolean")
	 * @var boolean $canBeEmpty
	 */
	protected $canBeEmpty;
	
	public function __construct(array $data)
	{
		if (!isset($data['validator_static_method'])) {
			throw new \InvalidArgumentException('You must define a "validator_static_method" attribute for each @RequestContent annotation.');
		}
		
		$this->validatorStaticMethod = trim($data['validator_static_method']);
		$this->canBeEmpty = isset($data['can_be_empty']) ? (boolean) $data['can_be_empty'] : false;
	}
	
	public function getValidatorStaticMethod()
	{
		return $this->validatorStaticMethod;
	}
	
	public function isCanBeEmpty()
	{
		return $this->canBeEmpty;
	}
	
	public static function getName()
	{
		return self::class;
	}
}