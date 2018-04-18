<?php
namespace Oka\ApiBundle\Model;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface UserInterface extends AdvancedUserInterface
{
	/**
	 * @return string
	 */
	public function getPlainPassword();
	
	/**
	 * @param string $password
	 */
	public function setPassword($password);
	
	/**
	 * @param string $salt
	 */
	public function setSalt($salt);
}
