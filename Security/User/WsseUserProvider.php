<?php
namespace Oka\ApiBundle\Security\User;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Oka\ApiBundle\Model\WsseUserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * 
 * @author cedrick
 * 
 */
class WsseUserProvider implements UserProviderInterface
{
	/**
	 * @var ObjectManager $objectManager
	 */
	protected $objectManager;
	
	/**
	 * @var EntityRepository $repository
	 */
	protected $repository;
	
	/**
	 * @var string $class
	 */
	protected $class;
	
	/**
	 * Wsse user provider Constructor
	 * 
	 * @param ObjectManager $om
	 * @param string $class
	 */
	public function __construct(ObjectManager $om, $class)
	{
		$metadata = $om->getClassMetadata($class);
		
		$this->objectManager = $om;
		$this->class = $metadata->getName();
		$this->repository = $om->getRepository($class);
	}
	
	/**
	 * Loads the user for the given username.
	 * 
	 * This method must throw UsernameNotFoundException if the user is not
	 * found.
	 * 
	 * @param string $username The username
	 * 
	 * @return UserInterface
	 * 
	 * @see UsernameNotFoundException
	 * 
	 * @throws UsernameNotFoundException if the user is not found
	 */
	public function loadUserByUsername($username)
	{
		if (!$client = $this->repository->findOneBy(['username' => $username])) {
			throw new UsernameNotFoundException(sprintf('Client Username "%s" does not exist.', $username));
		}
		
		return $client;
	}
	
	/**
	 * Refreshes the user for the account interface.
	 *
	 * It is up to the implementation to decide if the user data should be
	 * totally reloaded (e.g. from the database), or if the UserInterface
	 * object can just be merged into some internal array of users / identity
	 * map.
	 * @param UserInterface $user
	 *
	 * @return UserInterface
	 *
	 * @throws UnsupportedUserException if the account is not supported
	 */
	public function refreshUser(UserInterface $user)
	{
		$class = get_class($user);
		
		if (!$this->supportsClass($class)) {
			throw new UnsupportedUserException(sprintf('Les instances de "%s" ne sont pas supportées.', $class));
		}
		
		return $this->repository->find($user->getId());
	}
	
	/**
	 * Whether this provider supports the given user class
	 * 
	 * @param string $class
	 * 
	 * @return Boolean
	 */
	public function supportsClass($class)
	{
		return is_subclass_of($class, WsseUserInterface::class);
	}
}