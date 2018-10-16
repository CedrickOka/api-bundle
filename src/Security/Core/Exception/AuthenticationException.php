<?php
namespace Oka\ApiBundle\Security\Core\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class NonceExpiredException extends AuthenticationException
{
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\Exception\AuthenticationException::getMessageKey()
     */
    public function getMessageKey()
    {
        return 'Digest nonce has expired.';
    }
}
