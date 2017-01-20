<?php
namespace Oka\ApiBundle\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * 
 * @author cedrick
 * 
 */
class JSONWebTokenRequestMatcher implements RequestMatcherInterface
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
		return $request->headers->has('Authorization') && $request->getHost() === $this->host;
	}
}