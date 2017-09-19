<?php
namespace Oka\ApiBundle\Security\Nonce\Storage;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface NonceStorageInterface
{
	/**
	 * Starts the nonce.
	 * 
	 * @param string $nonceId
	 * 
	 * @return bool True if started
	 * 
	 * @throws \RuntimeException If something goes wrong starting the nonce.
	 */
	public function start($nonceId);
	
	/**
	 * Checks if the nonce is started.
	 * 
	 * @return bool True if started, false otherwise
	 */
	public function isStarted();
	
	/**
	 * Returns the nonce ID.
	 * 
	 * @return string The nonce ID or empty
	 */
	public function getId();
	
	/**
	 * Gets the timestamp at which the nonce was created.
	 * 
	 * @return int
	 * 
	 * @throws \RuntimeException If the nonce storage has not been started.
	 */
	public function getNonceTimestamp();
	
	/**
	 * Force the nonce to be saved and closed.
	 * 
	 * This method must invoke NonceHandlerInterface::write() and NonceHandlerInterface::close().
	 * 
	 * @param int $timestamp
	 * 
	 * @throws \RuntimeException If the nonce is saved without being started, or if the nonce storage
	 *                           is already closed.
	 */
	public function save($timestamp = null);
}
