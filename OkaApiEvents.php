<?php
namespace Oka\ApiBundle;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
final class OkaApiEvents
{
	/**
	 * The WSSE_USER_CREATED event occurs when the user is created with WsseUserManipulator.
	 *
	 * This event allows you to access the created user and to add some behaviour after the creation.
	 *
	 * @Event("Oka\ApiBundle\Event\WsseUserEvent")
	 */
	const WSSE_USER_CREATED = 'wsse.user.created';
	
	/**
	 * The WSSE_USER_PASSWORD_CHANGED event occurs when the user is created with UserManipulator.
	 *
	 * This event allows you to access the created user and to add some behaviour after the password change.
	 *
	 * @Event("Oka\ApiBundle\Event\WsseUserEvent")
	 */
	const WSSE_USER_PASSWORD_CHANGED = 'wsse.user.password_changed';

	/**
	 * The WSSE_USER_ACTIVATED event occurs when the user is created with WsseUserManipulator.
	 *
	 * This event allows you to access the activated user and to add some behaviour after the activation.
	 *
	 * @Event("Oka\ApiBundle\Event\WsseUserEvent")
	 */
	const WSSE_USER_ACTIVATED = 'wsse.user.activated';

	/**
	 * The WSSE_USER_DEACTIVATED event occurs when the user is created with WsseUserManipulator.
	 *
	 * This event allows you to access the deactivated user and to add some behaviour after the deactivation.
	 *
	 * @Event("Oka\ApiBundle\Event\WsseUserEvent")
	 */
	const WSSE_USER_DEACTIVATED = 'wsse.user.deactivated';
	
	/**
	 * The WSSE_USER_DELETED event occurs when the user is deleted with WsseUserManipulator.
	 *
	 * This event allows you to access the deleted user and to add some behaviour after the creation.
	 *
	 * @Event("Oka\ApiBundle\Event\WsseUserEvent")
	 */
	const WSSE_USER_DELETED = 'wsse.user.deleted';
}
