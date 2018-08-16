<?php
namespace Oka\ApiBundle\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oka\ApiBundle\Model\UserPasswordInterface;
use Oka\ApiBundle\Util\PasswordUpdaterInterface;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class UpdatePasswordSubscriber implements EventSubscriber
{
	/**
	 * @var PasswordUpdaterInterface $passwordUpdater
	 */
	private $passwordUpdater;
	
	public function __construct(PasswordUpdaterInterface $passwordUpdater) {
		$this->passwordUpdater = $passwordUpdater;
	}
	
	public function prePersist(LifecycleEventArgs $args) {
		$this->updatePassword($args);
	}
	
	public function preUpdate(PreUpdateEventArgs $args) {
		$this->updatePassword($args);
	}
	
	public function updatePassword(LifecycleEventArgs $args) {
		$object = $args->getObject();
		
		if ($object instanceof UserPasswordInterface) {
			$this->passwordUpdater->hashPassword($object);
		}
	}
	
	public function getSubscribedEvents() {
		return ['prePersist', 'preUpdate'];
	}
}
