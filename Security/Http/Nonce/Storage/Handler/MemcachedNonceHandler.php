<?php
namespace Oka\ApiBundle\Security\Nonce\Storage\Handler;

use Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class MemcachedNonceHandler implements NonceHandlerInterface
{
	/**
	 * @var \Memcached Memcached driver
	 */
	private $memcached;
	
	/**
	 * @var int Time to live in seconds
	 */
	private $ttl;
	
	/**
	 * @var string Key prefix for shared environments
	 */
	private $prefix;
	
	public function __construct(\Memcached $memcached, array $options = []) {
		if ($diff = array_diff(array_keys($options), ['prefix', 'expiretime'])) {
			throw new \InvalidArgumentException(sprintf(
					'The following options are not supported "%s"', implode(', ', $diff)
			));
		}
		
		$this->memcached = $memcached;
		$this->ttl = isset($options['expiretime']) ? (int) $options['expiretime'] : 86400;
		$this->prefix = isset($options['prefix']) ? $options['prefix'] : 'oka';
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::open()
	 */
	public function open($savePath, $nonceId)
	{
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::close()
	 */
	public function close()
	{
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::read()
	 */
	public function read($nonceId)
	{
		return (int) ($this->memcached->get($this->prefix.$nonceId) ?: 0);
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::write()
	 */
	public function write($nonceId, $nonceTime) {
		return $this->memcached->set($this->prefix.$nonceId, $nonceTime, 0, time() + $this->ttl);
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::destroy()
	 */
	public function destroy($nonceId) {
		$this->memcached->delete($this->prefix.$nonceId);
		
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::gc()
	 */
	public function gc(int $maxlifetime)
	{
		return true;
	}
}