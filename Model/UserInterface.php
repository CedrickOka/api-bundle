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
	public function getPlainPassword($password);
	
	public function setPassword($role);
	
	public function setSalt($salt);
}