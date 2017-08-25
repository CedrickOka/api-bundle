<?php
namespace Oka\ApiBundle\Model;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface WsseUserInterface extends AdvancedUserInterface
{
	const ROLE_DEFAULT = 'ROLE_API_USER';
	
	public function getId();
	
	public function setUsername($username);
	
	public function setPassword($password);
		
	public function hasRole($role);
		
	public function addRole($role);
	
	public function setRoles(array $roles);
		
	public function removeRole($role);
		
	public function hasAllowedIp($ip);
	
	public function getAllowedIps();
		
	public function addAllowedIp($ip);
	
	public function removeAllowedIp($ip);
	
	public function setEnabled($enabled);
	
	public function setLocked($locked);
}