<?php
namespace Oka\ApiBundle\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * 
 * @author cedrick
 * 
 */
class WsseRequestMatcher implements RequestMatcherInterface
{
	/**
	 * @var string $host
	 */
	private $host;
	
	public function __construct($host)
	{
		$this->host = $host;
	}
	
	public function matches(Request $request)
	{
		return $request->headers->has('X-WSSE') && $request->getHost() === $this->host;
	}
}