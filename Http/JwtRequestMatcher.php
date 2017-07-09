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
	 * @var HostRequestMatcher $hostMatcher
	 */
	private $hostMatcher;
	
	/**
	 * @var array $extractors
	 */
	private $extractors;
	
	public function __construct(HostRequestMatcher $hostMatcher, array $extractors)
	{
		$this->hostMatcher = $hostMatcher;
		$this->extractors = $extractors;
	}
	
	public function matches(Request $request)
	{
		if (false === $this->hostMatcher->matches($request)) {
			return false;
		}
		
		if ($this->extractors['authorization_header']['enabled'] && $request->headers->has($this->extractors['authorization_header']['name'])) {
			return true;
		}
		
		if ($this->extractors['query_parameter']['enabled'] && $request->query->has($this->extractors['query_parameter']['name'])) {
			return true;
		}
		
		if ($this->extractors['cookie']['enabled'] && $request->cookies->has($this->extractors['cookie']['name'])) {
			return true;
		}
		
		return false;
	}
}