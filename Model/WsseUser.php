<?php
namespace Oka\ApiBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * 
 * @author cedrick
 * 
 * @ORM\MappedSuperclass
 */
class WsseUser implements AdvancedUserInterface
{
	const ROLE_DEFAULT = 'ROLE_API_USER';
	
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
	 * @ORM\Column(type="string", nullable=true)
	 * @var string $salt
	 */
	protected $salt;
	
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
	
	public function __construct() {
		$this->roles = [];
		$this->enabled = true;
		$this->locked = false;
		$this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
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
		return $this->salt;
	}
	
	public function setSalt($salt) {
		$this->salt = $salt;
		return $this;
	}
	
	public function getRoles() {
		$roles = $this->roles;
		
		// we need to make sure to have at least one role
		$roles[] = static::ROLE_DEFAULT;
		
		return array_unique($roles);
	}
	
	public function addRole($role) {
		$role = strtoupper($role);
		
		if ($role !== static::ROLE_DEFAULT && !in_array($role, $this->roles, true)) {
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
	
	public function isEnabled() {
		return $this->enabled;
	}
}