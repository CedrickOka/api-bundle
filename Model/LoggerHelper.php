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
	 * @return string
	 */
	public static function logError(\Exception $exception)
	{
		return sprintf(
				'%s: %s (uncaught exception) at %s line %s',
				get_class($exception),
				$exception->getMessage(),
				$exception->getFile(),
				$exception->getLine()
		);
	}
	
	/**
	 * @param \Exception $exception
	 * @return string
	 * @deprecated
	 */
	public static function formatErrorMessage(\Exception $exception)
	{
		return self::logError($exception);
	}
}