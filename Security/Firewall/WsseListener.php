<?php
namespace Oka\ApiBundle\Security\Firewall;

use Oka\ApiBundle\Security\Authentication\Token\WsseUserToken;
use Oka\ApiBundle\Util\ErrorResponseFactory;
use Oka\ApiBundle\Util\LoggerHelper;
use Oka\ApiBundle\Util\RequestHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * 
 * @author cedrick
 * 
 */
class WsseListener extends LoggerHelper implements ListenerInterface
{
	/**
	 * @var TokenStorageInterface $tokenStorage
	 */
	protected $tokenStorage;
	
	/**
	 * @var AuthenticationManagerInterface $authenticationManager
	 */
	protected $authenticationManager;
	
	public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager)
	{
		$this->tokenStorage = $tokenStorage;
		$this->authenticationManager = $authenticationManager;
	}
	
	public function handle(GetResponseEvent $event)
	{
		$matches = [];
		$request = $event->getRequest();
		$headers = $request->headers;
		$failedMessage = Response::$statusTexts[Response::HTTP_FORBIDDEN];
		
		// Verifie que la requête comporte bien le header X-WSSE
		if ($headers->has('x-wsse') && preg_match('#UsernameToken Username="([^"]+)", PasswordDigest="([^"]+)", Nonce="([^"]+)", Created="([^"]+)"#', $headers->get('x-wsse'), $matches)) {
			$token = new WsseUserToken();
			$token->setUser($matches[1]);
			
			$token->digest 	= $matches[2];
			$token->nonce 	= $matches[3];
			$token->created = $matches[4];
			
			try {
				$authToken = $this->authenticationManager->authenticate($token);
				$this->tokenStorage->setToken($authToken);
				return;
			} catch (\Exception $e) {
				$failedMessage = $e->getMessage();
				$this->logDebug(sprintf('WSSE Login failed: %s', $e->getMessage()), [
						'username'	=> $token->getUsername(),
						'digest'	=> $token->digest,
						'nonce'		=> $token->nonce,
						'created'	=> $token->created
				]);
			}
		}
		
		// Deny authentication with a '403 Forbidden' HTTP response
		$format = RequestHelper::getFirstAcceptableFormat($request) ?: 'json';
		$event->setResponse(ErrorResponseFactory::create($failedMessage, Response::HTTP_FORBIDDEN, null, [], Response::HTTP_FORBIDDEN, ['X-Secure-With' => 'WSSE'], $format));
	}
}