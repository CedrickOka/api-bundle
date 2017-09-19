<?php
namespace Oka\ApiBundle\Security\Firewall;

use Oka\ApiBundle\Security\Authentication\Token\WsseUserToken;
use Oka\ApiBundle\Service\ErrorResponseFactory;
use Oka\ApiBundle\Util\LoggerHelper;
use Oka\ApiBundle\Util\RequestUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
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
	
	/**
	 * @var ErrorResponseFactory $errorFactory
	 */
	protected $errorFactory;
	
	/**
	 * @var TranslatorInterface $translator
	 */
	protected $translator;
	
	/**
	 * @param TokenStorageInterface $tokenStorage
	 * @param AuthenticationManagerInterface $authenticationManager
	 * @param ErrorResponseFactory $errorFactory
	 * @param TranslatorInterface $translator
	 */
	public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, ErrorResponseFactory $errorFactory, TranslatorInterface $translator)
	{
		$this->tokenStorage = $tokenStorage;
		$this->authenticationManager = $authenticationManager;
		$this->errorFactory = $errorFactory;
		$this->translator = $translator;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Http\Firewall\ListenerInterface::handle()
	 */
	public function handle(GetResponseEvent $event)
	{
		$request = $event->getRequest();
		$headers = $request->headers;
		
		$matches = [];
		$credentials = $headers->get('x-wsse', '');
		$failedMessage = Response::$statusTexts[Response::HTTP_UNAUTHORIZED];
		
		// Verifie que le header X-WSSE est bien définie
		if ($credentials !== '' && preg_match('#UsernameToken Username="([^"]+)", PasswordDigest="([^"]+)", Nonce="([^"]+)", Created="([^"]+)"#', $credentials, $matches)) {
			$preAuthToken = new WsseUserToken($matches[1], $credentials, []);
			$preAuthToken->setAttribute('digest', $matches[2]);
			$preAuthToken->setAttribute('nonce', $matches[3]);
			$preAuthToken->setAttribute('created', $matches[4]);
			
			try {
				$authToken = $this->authenticationManager->authenticate($preAuthToken);
				$this->tokenStorage->setToken($authToken);
				return;
				
			} catch (\Exception $e) {
				$failedMessage = $e->getMessage();				
				$this->logDebug(sprintf('Login with WS-Security failed, caused by : %s', $e->getMessage()), [
						'username'	=> $preAuthToken->getUsername(),
						'digest'	=> $preAuthToken->getAttribute('digest'),
						'nonce'		=> $preAuthToken->getAttribute('nonce'),
						'created'	=> $preAuthToken->getAttribute('created')
				]);
			}
		}
		
		// Deny authentication with a '401 Unauthorized' HTTP response
		$event->setResponse($this->errorFactory->create(
				$this->translator->trans($failedMessage, [], 'OkaApiBundle'), 
				Response::HTTP_UNAUTHORIZED, 
				null, 
				[], 
				Response::HTTP_UNAUTHORIZED, 
				['WWW-Authenticate' => 'WSSE realm="Secure Area", profile="UsernameToken"'], 
				RequestUtil::getFirstAcceptableFormat($request) ?: 'json'
		));
	}
}
