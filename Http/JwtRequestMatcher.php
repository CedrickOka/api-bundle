<?php
namespace Oka\ApiBundle\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * 
 * @author cedrick
 * 
 */
class JwtRequestMatcher implements RequestMatcherInterface
{
	/**
	 * @var string $host
	 */
	private $host;
	
	/**
	 * @var array $extractors
	 */
	private $extractors;
	
	public function __construct($host, array $extractors)
	{
		$this->host = $host;
		$this->extractors = $extractors;
	}
	
	public function matches(Request $request)
	{
		if ($this->extractors['authorization_header']['enabled'] && $request->headers->has($this->extractors['authorization_header']['name'])) {
			return $request->getHost() === $this->host;
		}
		
		if ($this->extractors['query_parameter']['enabled'] && $request->query->has($this->extractors['query_parameter']['name'])) {
			return $request->getHost() === $this->host;
		}
		
		if ($this->extractors['cookie']['enabled'] && $request->cookies->has($this->extractors['cookie']['name'])) {
			return $request->getHost() === $this->host;
		}
		
		return false;
	}
}