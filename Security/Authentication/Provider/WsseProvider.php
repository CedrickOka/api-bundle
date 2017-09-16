<?php
namespace Oka\ApiBundle\Security\Authentication\Provider;

use Oka\ApiBundle\Security\Authentication\Token\WsseUserToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Oka\ApiBundle\Security\Nonce\Storage\Nonce;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WsseProvider implements AuthenticationProviderInterface 
{
	/**
	 * @var UserProviderInterface $clientProvider
	 */
	private $clientProvider;
	
	/**
	 * @var string $cacheDir
	 */
	private $cacheDir;
	
	/**
	 * @var integer $lifetime
	 */
	private $lifetime;
	
	/**
	 * @param UserProviderInterface $clientProvider
	 * @param string $cacheDir
	 * @param int $lifetime
	 */
	public function __construct(UserProviderInterface $clientProvider, $cacheDir, $lifetime)
	{
		$this->clientProvider = $clientProvider;
		$this->cacheDir = $cacheDir;
		$this->lifetime = $lifetime;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface::authenticate()
	 */
	public function authenticate(TokenInterface $token)
	{
		try {
			/** @var \Symfony\Component\Security\Core\User\AdvancedUserInterface $client */
			$client = $this->clientProvider->loadUserByUsername($token->getUsername());
		} catch (AuthenticationException $e) {
			throw new BadCredentialsException('Bad credentials.');
		}
		
		if ($client instanceof AdvancedUserInterface) {
			if ($client->isEnabled() === false) {
				throw new DisabledException('Account is disabled.');
			}
			
			if ($client->isAccountNonLocked() === false) {
				throw new LockedException('Account is locked.');
			}
		}
		
		if (!$this->validateDigest($token->getAttribute('digest'), $token->getAttribute('nonce'), $token->getAttribute('created'), $client->getPassword())) {
			throw new BadCredentialsException('Bad credentials.');
		}
		
		return new WsseUserToken($client, $token->getCredentials(), $client->getRoles());
	}
	
	/**
	 * Valid digest password
	 * 
	 * @param string $digest
	 * @param string $nonce
	 * @param string $created
	 * @param string $secret
	 * @throws AuthenticationException
	 * @throws NonceExpiredException
	 * @return boolean
	 */
	protected function validateDigest($digest, $nonce, $created, $secret)
	{
		$currentTime = time();
		
		// Check that the created has not expired
		if (($currentTime < strtotime($created) - $this->lifetime) || ($currentTime > strtotime($created) + $this->lifetime)) {
			throw new AuthenticationException('Created timestamp is not valid.');
		}
		
		$nonceDecoded = base64_decode($nonce);
		$nonceFilePath = $this->cacheDir.'/'.$nonceDecoded;
		
		// Validate that the nonce is *not* used in the last 5 minutes
		// if it has, this could be a replay attack
		if (file_exists($nonceFilePath) && (((int) file_get_contents($nonceFilePath)) + $this->lifetime) > $currentTime) {
			throw new NonceExpiredException('Digest nonce has expired.');
		}
		
		if (!is_dir($this->cacheDir)) {
			mkdir($this->cacheDir, 0777, true);
		}
		
		file_put_contents($nonceFilePath, $currentTime, LOCK_EX);
		
		$expected = base64_encode(sha1($nonceDecoded.$created.$secret, true));
		
		// Valid the secret
		return hash_equals($expected, $digest);
	}
	
	public function supports(TokenInterface $token)
	{
		return $token instanceof WsseUserToken;
	}
}