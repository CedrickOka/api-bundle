<?php
namespace Oka\ApiBundle\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 * @deprecated 1.2.0 Not using
 */
class JwtRequestMatcher extends HostRequestMatcher
{
	/**
	 * @var array $extractors
	 */
	private $extractors;
	
	public function __construct($host, array $extractors)
	{
		parent::__construct($host);
		
		$this->extractors = $extractors;
	}
	
	public function matches(Request $request)
	{
		if (false === parent::matches($request)) {
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