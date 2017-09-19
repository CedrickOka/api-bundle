<?php
namespace Oka\ApiBundle\Security\Nonce\Storage\Handler;

use Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class FileNonceHandler implements NonceHandlerInterface
{
	/**
	 * @var string $savePath
	 */
	private $savePath;
	
	public function __construct($savePath)
	{
		$this->savePath = $savePath;
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::open()
	 */
	public function open($savePath, $nonceId)
	{
		if (null === $this->savePath) {
			$this->savePath = $savePath;
		}
		
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
		$filePath = $this->getFilePath($nonceId);
		
		return file_exists($filePath) ? (int) file_get_contents($filePath) : 0;
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::write()
	 */
	public function write($nonceId, $nonceTime)
	{
		if (!is_dir($this->savePath)) {
			mkdir($this->savePath, 0777, true);
		}
		
		return (bool) file_put_contents($this->getFilePath($nonceId), $nonceTime, LOCK_EX);
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::destroy()
	 */
	public function destroy($nonceId)
	{
		return unlink($this->getFilePath($nonceId));
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\ApiBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::gc()
	 */
	public function gc($maxlifetime)
	{
		return true;
	}
	
	/**
	 * @param int $nonceId
	 * @return string
	 */
	protected function getFilePath($nonceId)
	{
		return $this->savePath . '/' . $nonceId;
	}
}
