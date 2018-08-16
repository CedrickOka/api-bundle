<?php
namespace Oka\ApiBundle\Util;

use Oka\ApiBundle\Model\UserPasswordInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface PasswordUpdaterInterface
{
    /**
     * Updates the hashed password in the user when there is a new password.
     *
     * The implement should be a no-op in case there is no new password (it should not erase the
     * existing hash with a wrong one).
     *
     * @param UserPasswordInterface $user
     *
     * @return void
     */
	public function hashPassword(UserPasswordInterface $user);
}
