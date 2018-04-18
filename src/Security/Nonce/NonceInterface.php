<?php
namespace Oka\ApiBundle\Security\Nonce;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface NonceInterface
{
	/**
	 * @return string The nonce ID
	 */
	public function getId();
	
	/**
	 * @return int The timestamp at which the nonce was created
	 */
	public function getIssuedAt();
	
	/**
	 * Indicates whether the nonce is already registered in the nonce storage
	 * and validate that the nonce is *not* used in the last minutes equals at the lifetime
	 * 
	 * @param int $time The current timestamp
	 * @param int $lifetime The life time
	 * @return bool
	 */
	public function isAlreadyUsed($time, $lifetime);
	
	/**
	 * Save the nonce in the storage
	 * 
	 * @param int $timestamp
	 */
	public function save($timestamp = null);
}
