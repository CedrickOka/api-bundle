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
		return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
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
		if ($nonce === null) {
			$nonce = self::generateNonce();
		}
		$created = $created ? $created->format('c') : date('c');
		
		return sprintf(
				'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"', 
				$username, 
				self::digestPassword($password, $nonce.$created), 
				$nonce, 
				$created
		);
	}
}
