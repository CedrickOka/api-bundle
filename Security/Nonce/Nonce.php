<?php
namespace Oka\ApiBundle\Security\Nonce;

use Oka\ApiBundle\Security\Nonce\Storage\NonceStorageInterface;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class Nonce implements NonceInterface
{
	/**
	 * @var string $id
	 */
	private $id;
	
	/**
	 * @var NonceStorageInterface $storage
	 */
	private $storage;
	
	/**
	 * @param string $id
	 * @param NonceStorageInterface $storage
	 */
	public function __construct($id, NonceStorageInterface $storage)
	{
		$this->id = $id;
		$this->storage = $storage;
		$this->storage->start($id);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\NonceInterface::getId()
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\NonceInterface::isAlreadyUsed()
	 */
	public function isAlreadyUsed($time, $lifetime)
	{
		$nonceTimestamp = $this->storage->getNonceTimestamp();
		
		if ($nonceTimestamp) {
			return ($nonceTimestamp + $lifetime) > $time;
		}
		
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\NonceInterface::save()
	 */
	public function save($timestamp = null)
	{
		$this->storage->save($timestamp);
	}
}