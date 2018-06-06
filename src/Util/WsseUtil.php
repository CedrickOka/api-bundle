<?php
namespace Oka\ApiBundle\Util;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
final class WsseUtil
{
	/**
	 * Digests password
	 * 
	 * @param string $password
	 * @param string $salt
	 * @return string
	 */
	public static function digestPassword($password, $salt = '')
	{
		return base64_encode(sha1($salt.$password, true));
	}
	
	/**
	 * Generate Nonce
	 * 
	 * @return string
	 */
	public static function generateNonce()
	{
		return base64_encode(substr(md5(uniqid()), 0, 16));
	}
	
	/**
	 * Generate Token
	 * 
	 * @param string $username
	 * @param string $password
	 * @param string $nonce
	 * @param \DateTime $created
	 * @return string
	 */
	public static function generateToken($username, $password, $nonce = null, \DateTime $created = null)
	{
		if (null === $nonce) {
			$nonce = self::generateNonce();
		}
		
		$created = $created ? $created->format('c') : date('c');
		
		return sprintf(
				'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"', 
				$username, 
				self::digestPassword($password, base64_decode($nonce).$created), 
				$nonce, 
				$created
		);
	}
}
