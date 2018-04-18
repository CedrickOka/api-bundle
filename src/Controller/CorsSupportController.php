<?php
namespace Oka\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
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
