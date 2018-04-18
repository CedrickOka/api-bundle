<?php
namespace Oka\ApiBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 * @ORM\MappedSuperclass
 */
abstract class WsseUser implements WsseUserInterface
{
	/**
	 * @var mixed $id
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string", unique=true, length=128)
	 * @var string $username
	 */
	protected $username;
	
	/**
	 * @ORM\Column(type="string")
	 * @var string $password
	 */
	protected $password;
	
	/**
	 * @ORM\Column(type="array", nullable=true)
	 * @var array $roles
	 */
	protected $roles;
	
	/**
	 * @ORM\Column(type="boolean", options={"default": 1})
	 * @var boolean $enabled
	 */
	protected $enabled;
	
	/**
	 * @ORM\Column(type="boolean", options={"default": 0})
	 * @var boolean $locked
	 */
	protected $locked;
	
	/**
	 * @ORM\Column(name="allowed_ips", type="array", nullable=true)
	 * @var array $locked
	 */
	protected $allowedIps;
	
	public function __construct() {
		$this->roles = [];
		$this->enabled = true;
		$this->locked = false;
		$this->allowedIps = [];
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getUsername() {
		return $this->username;
	}
	
	public function setUsername($username) {
		$this->username = $username;
		return $this;
	}
	
	public function getPassword() {
		return $this->password;
	}
	
	public function setPassword($password) {
		$this->password = $password;
	}
	
	public function getSalt() {
		return null;
	}
	
	public function hasRole($role) {
		return in_array(strtoupper($role), $this->roles, true);
	}
	
	public function getRoles() {
		$roles = $this->roles;
		
		// we need to make sure to have at least one role
		$roles[] = static::ROLE_DEFAULT;
		
		return array_unique($roles);
	}
	
	public function addRole($role) {
		$role = strtoupper($role);
		
		if ($role !== static::ROLE_DEFAULT && false === in_array($role, $this->roles, true)) {
			$this->roles[] = $role;
		}
		
		return $this;
	}
	
	public function setRoles(array $roles) {
		$this->roles = [];
		
		foreach ($roles as $role) {
			$this->addRole($role);
		}
		
		return $this;
	}
	
	public function removeRole($role) {
		$role = strtoupper($role);
		
		if (false !== ($key = array_search($role, $this->roles, true))) {
			unset($this->roles[$key]);
			$this->roles = array_values($this->roles);
		}
		
		return $this;
	}
	
	public function isEnabled() {
		return $this->enabled;
	}
	
	public function setEnabled($enabled) {
		$this->enabled = $enabled;
		return $this;
	}
	
	public function setLocked($locked) {
		$this->locked = $locked;
		return $this;
	}
	
	public function eraseCredentials() {}
	
	public function isAccountNonExpired() {
		return true;
	}
	
	public function isAccountNonLocked() {
		return !$this->locked;
	}
	
	public function isCredentialsNonExpired() {
		return true;
	}
	
	public function hasAllowedIp($ip) {
		return in_array($ip, $this->allowedIps, true);
	}
	
	public function getAllowedIps() {
		return $this->allowedIps;
	}
	
	public function addAllowedIp($ip) {
		if (false === in_array($ip, $this->allowedIps, true)) {
			$this->allowedIps[] = $ip;
		}
		return $this;
	}
	
	public function setAllowedIps(array $allowedIps) {
		$this->allowedIps = [];
		foreach ($allowedIps as $ip) {
			$this->addAllowedIp($ip);
		}
		return $this;
	}
	
	public function removeAllowedIp($ip) {
		if (false !== ($key = array_search($ip, $this->allowedIps, true))) {
			unset($this->allowedIps[$key]);
			$this->allowedIps = array_values($this->allowedIps);
		}
		return $this;
	}
}
