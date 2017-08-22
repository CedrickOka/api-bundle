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
	 * @var string $created
	 */
	public $created;
	
	/**
	 * @var string $created
	 */
	public $digest;
	
	/**
	 * @var string $created
	 */
	public $nonce;
	
	public function __construct(array $roles = array())
	{
		parent::__construct($roles);
		
		// If the user has roles, consider it authenticated
		$this->setAuthenticated(count($roles) > 0);
	}
	
	public function getCredentials()
	{
		return '';
	}
}