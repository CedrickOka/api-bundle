<?php
namespace Oka\ApiBundle\Security\Nonce\Storage\Proxy;

use Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class NonceHandlerProxy implements NonceHandlerInterface
{
	/**
	 * @var NonceHandlerInterface $handler
	 */
	private $handler;
	
	/**
     * @var bool $active
     */
    private $active = false;
	
	public function __construct(NonceHandlerInterface $handler)
	{
		$this->handler = $handler;
	}
	
	/**
	 * @return bool
	 */
	public function isActive()
	{
		return $this->active;
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::open()
	 */
	public function open($savePath, $nonceId)
	{
		$return = (bool) $this->handler->open($savePath, $nonceId);
		
		if (true === $return) {
			$this->active = true;
		}
		
		return $return;
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::close()
	 */
	public function close()
	{
		$this->active = false;
		
		return (bool) $this->handler->close();
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::read()
	 */
	public function read($nonceId)
	{
		return (int) $this->handler->read($nonceId);
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::write()
	 */
	public function write($nonceId, $nonceTime)
	{
		return (bool) $this->handler->write($nonceId, $nonceTime);
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::destroy()
	 */
	public function destroy($nonceId)
	{
		return (bool) $this->handler->destroy($nonceId);
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::gc()
	 */
	public function gc($maxlifetime)
	{
		return (bool) $this->handler->gc($maxlifetime);
	}
}