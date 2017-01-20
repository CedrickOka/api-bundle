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
	
	public static function formatErrorMessage(\Exception $exception)
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