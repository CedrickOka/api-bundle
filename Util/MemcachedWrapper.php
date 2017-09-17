<?php
namespace Oka\ApiBundle\Util;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class MemcachedWrapper extends \Memcached
{
	/**
	 * Prevent adding of new servers as duplicates. We're persistent!
	 * 
	 * @param array $servers
	 * 
	 * @return bool
	 */
	public function addServers(array $servers)
	{
		if (0 === count($this->getServerList())) {
			return parent::addServers($servers);
		}
		
		return false;
	}
	
	/**
	 * Prevent adding of new server as duplicate. We're persistent!
	 * 
	 * @param string $host
	 * @param int    $port
	 * @param int    $weight
	 * 
	 * @return bool
	 */
	public function addServer($host, $port, $weight = 0)
	{
		foreach ($this->getServerList() as $server) {
			if ($server['host'] == $host && $server['port'] == $port) {
				return false;
			}
		}
		
		return parent::addServer($host, $port, $weight);
	}
}