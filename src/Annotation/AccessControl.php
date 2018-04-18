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
final class AccessControl
{
	/**
	 * @Attribute(name="protocol", required=true, type="string")
	 * @var string $version
	 */
	protected $protocol;
	
	/**
	 * @Attribute(name="version", required=true, type="string")
	 * @var string $version
	 */
	protected $version;
	
	/**
	 * @Attribute(name="formats", required=true, type="string")
	 * @var array $formats
	 */
	protected $formats;
	
	/**
	 * @var string
	 */
	private $versionNumber;

	/**
	 * @var string
	 */
	private $versionOperator;
	
	public function __construct(array $data)
	{
		$this->versionOperator = '==';
		
		if (!isset($data['protocol'])) {
			throw new \InvalidArgumentException('You must define a "protocol" attribute for each @AccessControl annotation.');
		}
		
		if (!isset($data['version'])) {
			throw new \InvalidArgumentException('You must define a "version" attribute for each @AccessControl annotation.');
		}
		
		if (!isset($data['formats'])) {
			throw new \InvalidArgumentException('You must define a "formats" attribute for each @AccessControl annotation.');
		}
		
		if (is_array($data['version'])) {
			if (!isset($data['version']['name'])) {
				throw new \InvalidArgumentException('You must define attribute "name" in "version" parameters for each @AccessApi annotation.');
			}
			
			$this->version = strtolower(trim($data['version']['name']));
			
			if (isset($data['version']['operator'])) {
				$this->versionOperator = trim($data['version']['operator']);
			}
		} else {
			$this->version = strtolower(trim($data['version']));
		}

		$this->protocol = strtolower(trim($data['protocol']));
		$this->versionNumber = self::findVersionNumber($this->version);
		$this->formats = array_map('trim', array_map('strtolower', explode(',', $data['formats'])));
	}
	
	/**
	 * @return string
	 */
	public static function getName()
	{
		return self::class;
	}
	
	/**
	 * @return string
	 */
	public function getProtocol()
	{
		return $this->protocol;
	}
	
	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * @return number
	 */
	public function getVersionNumber()
	{
		return $this->versionNumber;
	}
	
	/**
	 * @return string
	 */
	public function getVersionOperator()
	{
		return $this->versionOperator;
	}
	
	/**
	 * @return array
	 */
	public function getFormats()
	{
		return $this->formats;
	}
	
	/**
	 * @param string $versionName
	 * @return number
	 */
	private static function findVersionNumber($versionName) {
		return (int) preg_replace('#[^0-9]#', '', $versionName);
	}
}
