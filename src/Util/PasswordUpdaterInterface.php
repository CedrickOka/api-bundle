<?php
namespace Oka\ApiBundle\Util;

use Oka\ApiBundle\Model\UserPasswordUpdaterInterface;

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
     * @param UserPasswordUpdaterInterface $user
     *
     * @return void
     */
	public function hashPassword(UserPasswordUpdaterInterface $user);
}
