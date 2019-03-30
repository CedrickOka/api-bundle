<?php
namespace Oka\ApiBundle\Http;

use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WsseRequestMatcher extends HostRequestMatcher
{
	public function matches(Request $request)
	{
		if (false === parent::matches($request)) {
			return false;
		}
		
		if (true === $request->headers->has('X-WSSE')) {
			return true;
		}
		
		if (true === $request->headers->has('Authorization')) {
			return (bool) preg_match('#^UsernameToken (.+)$#i', $request->headers->get('Authorization'));
		}
		
		return false;
	}
}
