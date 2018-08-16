<?php
namespace Oka\ApiBundle\Util;

use Oka\ApiBundle\Model\UserPasswordUpdaterInterface;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class PasswordUpdater implements PasswordUpdaterInterface
{
	/**
	 * @var EncoderFactoryInterface $encoderFactory
	 */
	private $encoderFactory;
	
	public function __construct(EncoderFactoryInterface $encoderFactory) {
		$this->encoderFactory = $encoderFactory;
	}
	
	public function hashPassword(UserPasswordUpdaterInterface $user) {
		$plainPassword = $user->getPlainPassword();
		
		if (0 === strlen($plainPassword)) {
			return;
		}
		
		$encoder = $this->encoderFactory->getEncoder($user);
		$salt = !$encoder instanceof BCryptPasswordEncoder ? WsseUtil::generateNonce() : null;
		
		$user->setSalt($salt);
		$user->setPassword($encoder->encodePassword($plainPassword, $salt));
		
		if ($user instanceof UserInterface) {
			$user->eraseCredentials();
		}
	}
}
