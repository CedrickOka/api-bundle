<?php
namespace Oka\ApiBundle\Security\Nonce\Storage;

use Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface;
use Oka\ApiBundle\Security\Nonce\Storage\Proxy\NonceHandlerProxy;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class NativeNonceStorage implements NonceStorageInterface
{
	/**
	 * @var string $id
	 */
	protected $id;
	
	/**
	 * @var int $nonceTimestamp
	 */
	protected $nonceTimestamp;
	
	/**
	 * @var NonceHandlerProxy $saveHandler
	 */
	protected $saveHandler;
	
	/**
	 * @var string $savePath
	 */
	protected $savePath;
	
	/**
	 * @var bool $started
	 */
	protected $started = false;
	
	/**
	 * @var bool $started
	 */
	protected $closed = true;
	
	public function __construct($handler, $savePath = '')
	{
		$this->savePath = $savePath;
		$this->setSaveHandler($handler);
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
	 * {@inheritDoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\NonceStorageInterface::start()
	 */
	public function start($nonceId)
	{
		if ($this->started) {
			return true;
		}
		
		$this->id = $nonceId;		
		$this->loadNonce();
		
        return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\NonceStorageInterface::isStarted()
	 */
	public function isStarted()
	{
		return $this->started;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\NonceStorageInterface::getId()
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\NonceStorageInterface::getNonceTimestamp()
	 */
	public function getNonceTimestamp()
	{
		if ($this->saveHandler->isActive() && !$this->started) {
			$this->loadNonce();
		} elseif (!$this->started) {
			throw new \RuntimeException('Failed to get the nonce timestamp because the nonce storage has not been started.');
		}
		
		return $this->nonceTimestamp;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\NonceStorageInterface::save()
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
		$this->saveHandler->close();
		$this->closed = true;
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
	
	/**
	 * Load the nonce with timestamp.
	 */
	protected function loadNonce()
	{
		$this->saveHandler->open($this->savePath, $this->id);
		$this->nonceTimestamp = $this->saveHandler->read($this->id);
		
		$this->started = true;
		$this->closed = false;
	}
}
