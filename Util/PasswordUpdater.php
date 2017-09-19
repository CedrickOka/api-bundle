<?php
namespace Oka\ApiBundle\Util;

use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Oka\ApiBundle\Model\UserInterface;

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
	
	public function hashPassword(UserInterface $user) {
		$plainPassword = $user->getPlainPassword();
		
		if (0 === strlen($plainPassword)) {
			return;
		}
		
		$encoder = $this->encoderFactory->getEncoder($user);
		
		if ($encoder instanceof BCryptPasswordEncoder) {
			$user->setSalt(null);
		} else {
			$user->setSalt(WsseUtil::generateNonce());
		}
		
		$user->setPassword($encoder->encodePassword($plainPassword, $user->getSalt()));
		$user->eraseCredentials();
	}
}
