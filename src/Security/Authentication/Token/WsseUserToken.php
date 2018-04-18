<?php
namespace Oka\ApiBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WsseUserToken extends AbstractToken
{
	/**
	 * @var string $credentials
	 */
	private $credentials;
	
	/**
	 * @param mixed $user
	 * @param string $credentials
	 * @param array $roles
	 */
	public function __construct($user, $credentials = '', array $roles = [])
	{
		parent::__construct($roles);
		
		$this->setUser($user);
		$this->credentials = $credentials;
		
		// If the user has roles, consider it authenticated
		$this->setAuthenticated(count($roles) > 0);
	}
	
	public function getCredentials()
	{
		return $this->credentials;
	}
	
	public function eraseCredentials()
	{
		parent::eraseCredentials();
		
		$this->credentials = null;
	}
}
