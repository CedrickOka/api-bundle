<?php
namespace Oka\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @author cedrick
 * 
 */
class CorsSupportController extends Controller
{
	/**
	 * Cors Support
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function indexAction(Request $request)
	{
		return new Response('', 200, [
				'Access-Control-Allow-Methods' => $request->headers->get('Access-Control-Request-Method'),
				'Access-Control-Allow-Headers' => $request->headers->get('Access-Control-Request-Headers')
		]);
	}
}