<?php
namespace Oka\ApiBundle\Security\Authorization\Voter;

use Oka\ApiBundle\Model\WsseUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WsseUserAllowedIpsVoter extends Voter
{
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Core\Authorization\Voter\Voter::supports()
	 */
	public function supports($attribute, $subject)
	{
		return $subject instanceof Request;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Core\Authorization\Voter\Voter::voteOnAttribute()
	 */
	public function voteOnAttribute($attribute, $subject, TokenInterface $token) {}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Core\Authorization\Voter\Voter::vote()
	 */
	public function vote(TokenInterface $token, $object, array $attributes)
	{
		foreach ($attributes as $attribute) {
			if (false === $this->supports($attribute, $object)) {
				continue;
			}
			
			$user = $token->getUser();
			
			if ($user instanceof WsseUserInterface) {
				$allowedIps = $user->getAllowedIps();
				
				/** @var \Symfony\Component\HttpFoundation\Request $object */
				if (false === empty($allowedIps) && false === in_array($object->getClientIp(), $allowedIps, true)) {
					return VoterInterface::ACCESS_DENIED;
				}
				
				break;
			}
		}
		
		return VoterInterface::ACCESS_ABSTAIN;
	}
}