<?php
namespace Oka\ApiBundle\Event;

use Oka\ApiBundle\Model\WsseUser;
use Symfony\Component\EventDispatcher\Event;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WsseUserEvent extends Event
{
	protected $user;
	
	/**
	 * @param WsseUser $user
	 */
	public function __construct(WsseUser $user)
	{
		$this->user = $user;
	}
	
	/**
	 * @return \Oka\ApiBundle\Model\WsseUser
	 */
	public function getUser()
	{
		return $this->user;
	}
}