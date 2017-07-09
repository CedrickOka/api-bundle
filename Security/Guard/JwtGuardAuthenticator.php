<?php
namespace Oka\ApiBundle\Security\Guard;

use Aynid\UserBundle\Security\UserProvider;
use Oka\ApiBundle\Util\JSONWebTokenHelper;
use Oka\ApiBundle\Util\ResponseHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * 
 * @author cedrick
 * 
 */
class JwtGuardAuthenticator extends AbstractGuardAuthenticator
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
	 * @var ResponseHelper $responseHelper
	 */
	private $responseHelper;
	
	public function __construct(JSONWebTokenHelper $jwtHelper, $userClass, ResponseHelper $responseHelper)
	{
		$this->jwtHelper = $jwtHelper;
		$this->userClass = $userClass;
		$this->responseHelper = $responseHelper;
	}
	
	public function getCredentials(Request $request)
	{
		$matches = [];
		
		if ($request->headers->has('Authorization') && preg_match('#Bearer (.+)$#', $request->headers->get('Authorization'), $matches)) {			
			/** @var \Lcobucci\JWT\Token $jwtToken */
			$jwtToken = $this->jwtHelper->parse($matches[1]);
			
			if ($jwtToken->hasClaim('jti') && !$jwtToken->isExpired()) {								
				return ['username' => $jwtToken->getClaim('jti'), 'token' => $jwtToken];
			}
		}
	}
	
	public function getUser($credentials, UserProviderInterface $userProvider)
	{
		/** @var \Lcobucci\JWT\Token $jwtToken */
		$jwtToken = $credentials['token'];
		$user = $userProvider->loadUserByUsername($credentials['username']);
		
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
		return $this->jwtHelper->verify($user, $credentials['token']);
	}
	
	public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
	{
		return null;
	}
	
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
	{
		return $this->responseHelper->getAcceptableResponse(
				$request, 
				ResponseHelper::createError(Response::HTTP_FORBIDDEN, $exception->getMessage()), 
				Response::HTTP_FORBIDDEN, 
				['X-Secure-With' => 'JWT'], 
				'json'
			);
	}
	
	public function start(Request $request, AuthenticationException $authException = null)
	{
		return $this->responseHelper->getAcceptableResponse(
				$request, 
				ResponseHelper::createError(Response::HTTP_UNAUTHORIZED, 'Authentication Required.'), 
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