<?php
namespace Oka\ApiBundle\Security\Guard;

use Aynid\UserBundle\Security\UserProvider;
use Oka\ApiBundle\Util\JSONWebTokenHelper;
use Oka\ApiBundle\Util\ResponseHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;

/**
 * 
 * @author cedrick
 * 
 */
class JSONWebTokenGuardAuthenticator extends AbstractGuardAuthenticator
{
	/**
	 * @var JSONWebTokenHelper $jwtHelper
	 */
	private $jwtHelper;
	
	/**
	 * @var string $userClass
	 */
	private $userClass;
	
	/**
	 * @var string $authIdKey
	 */
	private $authIdKey;
	
	/**
	 * @var string $authMethodName
	 */
	private $authMethodName;
	
	/**
	 * @var ResponseHelper $responseHelper
	 */
	private $responseHelper;
	
	public function __construct(JSONWebTokenHelper $jwtHelper, $userClass, $authIdKey, $authMethodName, ResponseHelper $responseHelper)
	{
		$this->jwtHelper = $jwtHelper;
		$this->userClass = $userClass;
		$this->authIdKey = $authIdKey;
		$this->authMethodName = $authMethodName;
		$this->responseHelper = $responseHelper;
	}
	
	public function getCredentials(Request $request)
	{
		$matches = [];
		
		if ($request->headers->has('Authorization') && preg_match('#Bearer (.+)$#', $request->headers->get('Authorization'), $matches)) {			
			/** @var \Lcobucci\JWT\Token $jwtToken */
			$jwtToken = $this->jwtHelper->parse($matches[1]);
			
			if ($jwtToken->hasClaim('jti') && !$jwtToken->isExpired()) {
				if (!$authId = $request->attributes->get($this->authIdKey, null)) {
					if (!$authId = $request->query->get($this->authIdKey, null)) {
						$authId = $request->headers->get('X-Auth-ID', null);
					}
				}
				
				return $authId !== null ? ['authId' => $authId, 'token' => $jwtToken] : null;
			}
		}
	}
	
	public function getUser($credentials, UserProviderInterface $userProvider)
	{
		/** @var \Lcobucci\JWT\Token $jwtToken */
		$jwtToken = $credentials['token'];
		$user = $userProvider->loadUserByUsername($jwtToken->getClaim('username'));
		
		if (!$user instanceof $this->userClass) {
			throw new AuthenticationServiceException(sprintf('Authentication request could not be processed, user loaded by provider "%s" is not instance of class "%s".', get_class($userProvider), $this->userClass));
		}
		
		if (!method_exists($user, $this->authMethodName)) {
			throw new AuthenticationServiceException(sprintf('Authentication request could not be processed, user loaded by provider "%s" doesn\'t contains method "%s".', get_class($userProvider), $this->authMethodName));
		}
		
		return $user;
	}
	
	public function checkCredentials($credentials, UserInterface $user)
	{
		return $user->$this->authMethodName() === $credentials['authId'] && $this->jwtHelper->verify($user, $credentials['token']);
	}
	
	public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
	{
		return null;
	}
	
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
	{
		return $this->responseHelper->getAcceptableResponse(
				$request, 
				ResponseHelper::buildErrorMessage(Response::HTTP_FORBIDDEN, $exception->getMessage()), 
				Response::HTTP_FORBIDDEN, 
				['X-Secure-With' => 'JWT'], 
				'json'
			);
	}
	
	public function start(Request $request, AuthenticationException $authException = null)
	{
		return $this->responseHelper->getAcceptableResponse(
				$request, 
				ResponseHelper::buildErrorMessage(Response::HTTP_UNAUTHORIZED, 'Authentication Required.'), 
				Response::HTTP_UNAUTHORIZED, 
				['X-Secure-With' => 'JWT'], 
				'json'
			);
	}
	
	public function supportsRememberMe()
	{
		return false;
	}
}