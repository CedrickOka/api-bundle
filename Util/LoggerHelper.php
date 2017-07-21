<?php
namespace Oka\ApiBundle\Util;

use Psr\Log\LoggerInterface;

/**
 * 
 * @author cedrick
 * 
 */
abstract class LoggerHelper
{
	/**
	 * @var LoggerInterface $logger
	 */
	protected $logger;
	
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
	
	/**
	 * @param \Exception $exception
	 */
	public function logErrException(\Exception $exception, array $context = [])
	{
		$this->logger->error(self::convertExceptionToMessage($exception), $context);
	}
	
	/**
	 * @param \Exception $exception
	 */
	public function logWarnException(\Exception $exception, array $context = [])
	{
		$this->logger->warning(self::convertExceptionToMessage($exception), $context);
	}
	
	/**
	 * @param \Exception $exception
	 */
	public function logDebException(\Exception $exception, array $context = [])
	{
		$this->logger->debug(self::convertExceptionToMessage($exception), $context);
	}
	
	/**
	 * @param string $message
	 * @param array $context
	 */
	public function logError($message, array $context = [])
	{
		$this->logger->error($message, $context);
	}
	
	/**
	 * @param string $message
	 * @param array $context
	 */
	public function logWarning($message, array $context = [])
	{
		$this->logger->warning($message, $context);
	}
	
	/**
	 * @param string $message
	 * @param array $context
	 */
	public function logDebug($message, array $context = [])
	{
		$this->logger->debug($message, $context);
	}
	
	/**
	 * @param \Exception $exception
	 * @return string
	 */
	public static function convertExceptionToMessage(\Exception $exception)
	{
		return sprintf(
				'%s: %s (uncaught exception) at %s line %s',
				get_class($exception),
				$exception->getMessage(),
				$exception->getFile(),
				$exception->getLine()
		);
	}
}