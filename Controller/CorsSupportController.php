<?php
namespace Oka\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @author cedrick
 * 
 */
class CorsSupportController extends Controller
{
	/**
	 * Cors Support Test
	 * 
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function indexAction()
	{
		return new Response();
	}
}