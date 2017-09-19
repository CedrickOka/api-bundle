<?php
namespace Oka\ApiBundle\Util;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Oka\ApiBundle\Event\WsseUserEvent;
use Oka\ApiBundle\Model\WsseUserInterface;
use Oka\ApiBundle\OkaApiEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Executes some manipulations on the WSSE users.
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WsseUserManipulator
{
	/**
	 * @var ObjectManager $objectManager
	 */
	private $objectManager;
	
	/**
	 * Wsse User class.
	 * 
	 * @var string $class
	 */
	private $class;

	/**
	 * @var EventDispatcherInterface
	 */
	private $dispatcher;
	
	/**
	 * @var ObjectRepository $objectRepository
	 */
	private $objectRepository;

	/**
	 * WsseUserManipulator constructor.
	 * 
	 * @param ObjectManager				$om
	 * @param EventDispatcherInterface	$dispatcher
	 * @param string					$class
	 */
	public function __construct(ObjectManager $om, EventDispatcherInterface $dispatcher, $class)
	{
		$this->objectManager = $om;
		$this->dispatcher = $dispatcher;
		$this->objectRepository = $om->getRepository($class);
		$metadata = $om->getClassMetadata($class);
		$this->class = $metadata->getName();
	}

	/**
	 * Creates a user and returns it.
	 * 
	 * @param string $username
	 * @param string $password
	 * @param bool   $active
	 * @param array  $roles
	 * 
	 * @return \Oka\ApiBundle\Model\WsseUserInterface
	 */
	public function create($username, $password, $active, array $roles = [])
	{
		/** @var \Oka\ApiBundle\Model\WsseUserInterface $user */
		$user = new $this->class();
		$user->setUsername($username);
		$user->setPassword($password);
		$user->setEnabled((boolean) $active);
		$user->setRoles($roles);
		
		$this->saveUser($user);
		$this->dispatcher->dispatch(OkaApiEvents::WSSE_USER_CREATED, new WsseUserEvent($user));
		
		return $user;
	}
	
	/**
	 * Activates the given user.
	 * 
	 * @param string $username
	 */
	public function activate($username)
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		$user->setEnabled(true);
		
		$this->saveUser($user);
		$this->dispatcher->dispatch(OkaApiEvents::WSSE_USER_ACTIVATED, new WsseUserEvent($user));
	}

	/**
	 * Deactivates the given user.
	 * 
	 * @param string $username
	 */
	public function deactivate($username)
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		$user->setEnabled(false);
		
		$this->saveUser($user);
		$this->dispatcher->dispatch(OkaApiEvents::WSSE_USER_DEACTIVATED, new WsseUserEvent($user));
	}

	/**
	 * Changes the password for the given user.
	 * 
	 * @param string $username
	 * @param string $password
	 */
	public function changePassword($username, $password)
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		$user->setPassword($password);
		
		$this->saveUser($user);
		$this->dispatcher->dispatch(OkaApiEvents::WSSE_USER_PASSWORD_CHANGED, new WsseUserEvent($user));
	}
	
	/**
	 * Adds role to the given user.
	 * 
	 * @param string $username
	 * @param string $role
	 * 
	 * @return bool true if role was added, false if user already had the role
	 */
	public function addRole($username, $role)
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		
		if ($user->hasRole($role)) {
			return false;
		}
		
		$user->addRole($role);
		$this->saveUser($user);
		
		return true;
	}

	/**
	 * Removes role from the given user.
	 * 
	 * @param string $username
	 * @param string $role
	 * 
	 * @return bool true if role was removed, false if user didn't have the role
	 */
	public function removeRole($username, $role)
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		
		if (!$user->hasRole($role)) {
			return false;
		}
		
		$user->removeRole($role);
		$this->saveUser($user);
		
		return true;
	}
	
	/**
	 * Adds $ip to the given user.
	 * 
	 * @param string $username
	 * @param string $ip
	 * 
	 * @return bool true if $ip was added, false if user already had the $ip
	 */
	public function addAllowedIp($username, $ip)
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		
		if ($user->hasAllowedIp($ip)) {
			return false;
		}
		
		$user->addAllowedIp($ip);
		$this->saveUser($user);
		
		return true;
	}

	/**
	 * Removes $ip from the given user.
	 * 
	 * @param string $username
	 * @param string $ip
	 * 
	 * @return bool true if $ip was removed, false if user didn't have the $ip
	 */
	public function removeAllowedIp($username, $ip)
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		
		if (!$user->hasAllowedIp($ip)) {
			return false;
		}
		
		$user->removeAllowedIp($ip);
		$this->saveUser($user);
		
		return true;
	}
	
	/**
	 * Deletes the given user.
	 * 
	 * @param string $username
	 */
	public function delete($username)
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		$this->objectManager->remove($user);
		$this->objectManager->flush($user);
		
		$this->dispatcher->dispatch(OkaApiEvents::WSSE_USER_DELETED, new WsseUserEvent($user));
	}

	/**
	 * Finds a user by his username and throws an exception if we can't find it.
	 * 
	 * @param string $username
	 * 
	 * @throws \InvalidArgumentException When user does not exist
	 * 
	 * @return \Oka\ApiBundle\Model\WsseUserInterface
	 */
	private function findUserByUsernameOrThrowException($username)
	{
		if (!$user = $this->objectRepository->findOneBy(['username' => $username])) {
			throw new \InvalidArgumentException(sprintf('User identified by "%s" username does not exist.', $username));
		}
		
		return $user;
	}
	
	/**
	 * Save user in database
	 * 
	 * @param WsseUserInterface $user
	 * 
	 * @return \Oka\ApiBundle\Model\WsseUserInterface
	 */
	private function saveUser(WsseUserInterface $user)
	{
		if (false === $this->objectManager->contains($user)) {
			$this->objectManager->persist($user);
		}
		$this->objectManager->flush($user);
	}
}
