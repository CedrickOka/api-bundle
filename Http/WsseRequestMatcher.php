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
		
		return $request->headers->has('X-WSSE');
	}
}