<?php
namespace Oka\ApiBundle\Model;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
interface UserPasswordUpdaterInterface
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
