<?php
namespace Oka\ApiBundle\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class HostRequestMatcher implements RequestMatcherInterface
{
	/**
	 * @var string $host
	 */
	private $host;
	
	public function __construct($host)
	{
		$this->host = $host;
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Symfony\Component\HttpFoundation\RequestMatcherInterface::matches()
	 */
	public function matches(Request $request)
	{
		return $this->host === null || $request->getHost() === $this->host;
	}
}
