<?php
namespace Oka\ApiBundle\Security\Nonce;

use Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface;
use Oka\ApiBundle\Security\Nonce\Storage\Proxy\NonceHandlerProxy;

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
	 * @var int $issuedAt
	 */
	private $issuedAt;
	
	/**
	 * @var NonceHandlerProxy $saveHandler
	 */
	private $saveHandler;
	
	/**
	 * @var string $savePath
	 */
	private $savePath;
	
	/**
	 * @var bool $started
	 */
	private $started = false;
	
	/**
	 * @var bool $started
	 */
	private $closed = true;
	
	/**
	 * @param string $id
	 * @param mixed $handler
	 * @param string $savePath
	 */
	public function __construct($id, $handler, $savePath = '')
	{
		$this->id = $id;
		$this->savePath = $savePath;
		$this->setSaveHandler($handler);
		$this->start();
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
	 * @see \Oka\ApiBundle\Security\Nonce\NonceInterface::getIssuedAt()
	 */
	public function getIssuedAt()
	{
		if ($this->saveHandler->isActive() && !$this->started) {
			$this->start();
		} elseif (!$this->started) {
			throw new \RuntimeException('Failed to get the nonce timestamp because the nonce storage has not been started.');
		}
		
		if (!$this->issuedAt) {
			$this->issuedAt = $this->saveHandler->read($this->id);
		}
		
		return $this->issuedAt;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\NonceInterface::isAlreadyUsed()
	 */
	public function isAlreadyUsed($time, $lifetime)
	{
		if ($issuedAt = $this->getIssuedAt()) {
			return ($issuedAt + $lifetime) > $time;
		}
		
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\NonceInterface::save()
	 */
	public function save($timestamp = null)
	{
		if (!$this->started) {
			throw new \RuntimeException('Failed to save the nonce because the nonce storage has not been started.');
		}
		
		if ($this->closed) {
			throw new \RuntimeException('Failed to save the nonce because the nonce storage has been closed.');
		}
		
		$this->saveHandler->write($this->id, $timestamp ?: time());
		$this->close();
	}
	
	/**
	 * Gets the save handler instance.
	 * 
	 * @return NonceHandlerProxy
	 */
	public function getSaveHandler()
	{
		return $this->saveHandler;
	}
	
	/**
	 * Registers nonce save handler.
	 * 
	 * @param NonceHandlerProxy|NonceHandlerInterface $saveHandler
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function setSaveHandler($saveHandler)
	{
		if (!$saveHandler instanceof NonceHandlerProxy &&
			!$saveHandler instanceof NonceHandlerInterface) {
			throw new \InvalidArgumentException('Must be instance of NonceHandlerProxy; or implement NonceHandlerInterface.');
		}
		
		// Wrap $saveHandler in proxy and prevent double wrapping of proxy
		if (!$saveHandler instanceof NonceHandlerProxy && $saveHandler instanceof NonceHandlerInterface) {
			$saveHandler = new NonceHandlerProxy($saveHandler);
		}
		
		$this->saveHandler = $saveHandler;
	}
	
	protected function start()
	{
		if ($this->started) {
			return true;
		}
		
		if (!$this->saveHandler->open($this->savePath)) {
			throw new \RuntimeException('Failed to start the nonce storage.');
		}
		
		$this->started = true;
		$this->closed = false;
		
		return true;
	}
	
	protected function close()
	{
		if ($this->closed) {
			return true;
		}
		
		if (!$this->saveHandler->close()) {
			throw new \RuntimeException('Failed to close the nonce storage.');
		}
		
		$this->started = false;
		$this->closed = true;
		
		return true;
	}
}
