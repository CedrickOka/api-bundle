<?php
namespace Oka\ApiBundle\Util;

use Oka\ApiBundle\Service\LoggerHelper;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 * @deprecated 1.2.0 Not using
 * 
 */
class JSONWebTokenHelper extends LoggerHelper
{
	/**
	 * @var string $host
	 */
	protected $host;
	
	/**
	 * @var string $secret
	 */
	protected $secret;
	
	public function __construct($host, $secret)
	{
		$this->host = $host;
		$this->secret = $secret;
	}
	
	/**
	 * @param UserInterface $user
	 * @return \Lcobucci\JWT\Token
	 */
	public function generateJSONWebToken(UserInterface $user)
	{
		$time = time();
		$jwtBuilder = new \Lcobucci\JWT\Builder();
		$signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();
		
		return $jwtBuilder->setIssuer($this->host)
						  ->setId($user->getUsername())
						  ->setIssuedAt($time)
						  ->setExpiration($time + 3600)
						  ->set('username', $user->getUsername())
// 						  ->set('usernameCanonical', $user->getUsernameCanonical())
// 						  ->set('email', $user->getEmail())
						  ->sign($signer, $this->getKey($user))
						  ->getToken();
	}
	
	/**
	 * @param string $token
	 * @return \Lcobucci\JWT\Token
	 */
	public function parse($token)
	{
		$jwtParser = new \Lcobucci\JWT\Parser();
		
		return $jwtParser->parse((string) $token);
	}
	
	/**
	 * @param UserInterface $user
	 * @param \Lcobucci\JWT\Token $jwtToken
	 * @return boolean
	 */
	public function verify(UserInterface $user, $jwtToken)
	{
		$signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();
		
		try {
			if ($jwtToken->verify($signer, $this->getKey($user))) {
				$data = new \Lcobucci\JWT\ValidationData();
				$data->setIssuer($this->host);
				
				return $jwtToken->validate($data);
			}
		} catch (\Exception $e) {
			$this->logErrException($e);
		}
		
		return false;
	}
	
	/**
	 * @param UserInterface $user
	 */
	public function getKey(UserInterface $user)
	{
		return sprintf('%s_{%s}_%s', $user->getUsername(), $user->getSalt(), $this->secret);
	}
}