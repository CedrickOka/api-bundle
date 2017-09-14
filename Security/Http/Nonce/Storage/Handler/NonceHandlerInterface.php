<?php
namespace Oka\ApiBundle\Security\Nonce\Storage\Handler;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface NonceHandlerInterface
{
	/**
	 * Initialize session
	 * 
	 * @param string $savePath <p>
	 * The path where to store/retrieve the nonce.
	 * </p>
	 * @param string $nonceId <p>
	 * The nonce id.
	 * </p>
	 * @return bool The return value (usually true on success, false on failure).
	 */
	abstract public function open($savePath, $nonceId){}
	
	/**
	 * Close the session
	 * 
	 * @return bool The return value (usually true on success, false on failure).
	 */
	abstract public function close(){}
	
	/**
	 * Read nonce
	 * 
	 * @param string $nonceId <p>
	 * The nonce id.
	 * </p>
	 * @return int The timestamp at which the nonce was created. If nothing was read, it must return 0.
	 */
	abstract public function read($nonceId){}

	/**
	 * Write nonce
	 * 
	 * @param string $nonceId <p>
	 * The nonce id.
	 * </p>
	 * @param int $nonceTime <p>
	 * The timestamp at which the nonce was created.
	 * </p>
	 * @return bool The return value (usually true on success, false on failure).
	 */
	abstract public function write($nonceId, $nonceTime){}

	/**
	 * Destroy a session
	 * 
	 * @param string $nonceId <p>
	 * The nonce ID being destroyed.
	 * </p>
	 * @return bool The return value (usually true on success, false on failure).
	 */
	abstract public function destroy($nonceId){}

	/**
	 * Cleanup old nonces
	 * 
	 * @param int $maxlifetime <p>
	 * Nonces that have not updated for the last maxlifetime seconds will be removed.
	 * </p>
	 * @return bool The return value (usually true on success, false on failure).
	 */
	abstract public function gc($maxlifetime){}
}