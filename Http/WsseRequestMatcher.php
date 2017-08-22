<?php
namespace Oka\ApiBundle\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WsseRequestMatcher implements RequestMatcherInterface
{
	/**
	 * @var HostRequestMatcher $hostMatcher
	 */
	private $hostMatcher;
	
	public function __construct(HostRequestMatcher $hostMatcher)
	{
		$this->hostMatcher = $hostMatcher;
	}
	
	public function matches(Request $request)
	{
		if (false === $this->hostMatcher->matches($request)) {
			return false;
		}
		
		return $request->headers->has('X-WSSE');
	}
}