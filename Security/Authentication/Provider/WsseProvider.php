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
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * 
 * @author cedrick
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
	
	public function __construct(UserProviderInterface $clientProvider, $cacheDir, $lifetime) {
		$this->clientProvider = $clientProvider;
		$this->cacheDir = $cacheDir;
		$this->lifetime = $lifetime;
	}
	
	public function authenticate(TokenInterface $token)
	{
		if (!$client = $this->clientProvider->loadUserByUsername($token->getUsername())) {
			throw new UsernameNotFoundException(sprintf('API client "%s" could not be found.', $token->getUsername()));
		}
		
		if ($client->isEnabled() === false) {
			throw new DisabledException('API client account is disabled.');
		}
		
		if ($client->isAccountNonLocked() === false) {
			throw new LockedException('API client account is locked.');
		}
		
		if ($this->validateDigest($token->digest, $token->nonce, $token->created, $client->getPassword())) {
			$authenticatedToken = new WsseUserToken($client->getRoles());
			$authenticatedToken->setUser($client);
			
			return $authenticatedToken;
		}
		
		throw new BadCredentialsException('Invalid credentials.');
	}
	
	protected function validateDigest($digest, $nonce, $created, $secret)
	{
		$serverActualTime = time();
		// Verifie si le temps n'est pas dans le futur
		if (strtotime($created) > $serverActualTime) {
			throw new AuthenticationException('Back to the future...');
		}
		
		// Expire le timestamp après le temps defini par le parametre $lifetime
		if ($serverActualTime - strtotime($created) > $this->lifetime) {
			throw new AuthenticationException('Too late for this timestamp... Watch your watch.');
		}
		
		$nonceDecoded = base64_decode($nonce);
		$nonceFilePath = $this->cacheDir.'/'.$nonceDecoded;
		
		// Valide que le nonce est unique dans le temps defini par le parametre $lifetime
		if (file_exists($nonceFilePath) && (((int) file_get_contents($nonceFilePath)) + $this->lifetime) > $serverActualTime) {
			throw new NonceExpiredException('Digest nonce has expired.');
		}
		
		if (!is_dir($this->cacheDir)) {
			mkdir($this->cacheDir, 0777, true);
		}
		file_put_contents($nonceFilePath, $serverActualTime, LOCK_EX);
		
		// Valide le secret
		$expected = base64_encode(sha1($nonceDecoded.$created.$secret, true));
		
		return hash_equals($expected, $digest);
	}
	
	public function supports(TokenInterface $token)
	{
		return $token instanceof WsseUserToken;
	}
}