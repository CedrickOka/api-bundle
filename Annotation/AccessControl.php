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
			
			$this->version = $data['version'];
			$this->versionNumber = strtolower(trim($this->version['name']));
			
			if (isset($this->version['operator'])) {
				$this->versionOperator = trim($this->version['operator']);
			}
		} else {
			$this->version = strtolower(trim($data['version']));
			$this->versionNumber = $this->version;
			$this->versionOperator = '==';
		}
		
		$this->protocol = strtolower(trim($data['protocol']));
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
	 * @return string
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
}