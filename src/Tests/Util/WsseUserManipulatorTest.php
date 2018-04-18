<?php
namespace Oka\ApiBundle\Tests\Util;

use Oka\ApiBundle\Security\User\WsseUserProvider;
use Oka\ApiBundle\Util\WsseUserManipulator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WsseUserManipulatorTest extends KernelTestCase
{
	/**
	 * @var WsseUserManipulator $userManipulator
	 */
	protected $userManipulator;
	
	/**
	 * @var WsseUserProvider $userProvider
	 */
	protected $userProvider;
	
	protected function setUp()
	{
		self::bootKernel();
		
		$this->userManipulator = static::$kernel->getContainer()->get('oka_api.util.wsse_user_manipulator');
		$this->userProvider = static::$kernel->getContainer()->get('oka_api.wsse_user_provider');
	}
	
	protected function tearDown()
	{
		parent::tearDown();
	}
	
	/**
	 * @covers WsseUserManipulator::create
	 */
	public function testCreate()
	{
		$username = 'client_test';
		$this->userManipulator->create($username, $username, true);
		$user = $this->userProvider->loadUserByUsername($username);
		
		$this->assertEquals('client_test', $user->getUsername());
		$this->assertEquals('client_test', $user->getPassword());
		$this->assertEquals(true, $user->isEnabled());
		
		return $username;
	}
	
	/**
	 * @covers WsseUserManipulator::activate
	 * @depends testCreate
	 */
	public function testActivate($username)
	{
		$this->userManipulator->activate($username);
		$user = $this->userProvider->loadUserByUsername($username);
				
		$this->assertEquals(true, $user->isEnabled());
		
		return $username;
	}
	
	/**
	 * @covers WsseUserManipulator::deactivate
	 * @depends testActivate
	 */
	public function testDeactivate($username)
	{
		$this->userManipulator->deactivate($username);
		$user = $this->userProvider->loadUserByUsername($username);
				
		$this->assertEquals(false, $user->isEnabled());
		
		return $username;
	}
	
	/**
	 * @covers WsseUserManipulator::changePassword
	 * @depends testDeactivate
	 */
	public function testChangePassword($username)
	{
		$this->userManipulator->changePassword($username, 'new_password');
		$user = $this->userProvider->loadUserByUsername($username);
				
		$this->assertEquals('new_password', $user->getPassword());
		
		return $username;
	}
	
	/**
	 * @covers WsseUserManipulator::addRole
	 * @depends testChangePassword
	 */
	public function testAddRole($username)
	{
		$this->userManipulator->addRole($username, 'ROLE_TEST');
		$user = $this->userProvider->loadUserByUsername($username);
				
		$this->assertEquals(true, $user->hasRole('ROLE_TEST'));
		
		return $username;
	}
	
	/**
	 * @covers WsseUserManipulator::removeRole
	 * @depends testAddRole
	 */
	public function testRemoveRole($username)
	{
		$this->userManipulator->removeRole($username, 'ROLE_TEST');
		$user = $this->userProvider->loadUserByUsername($username);
		
		$this->assertEquals(false, $user->hasRole('ROLE_TEST'));
		
		return $username;
	}
	
	/**
	 * @covers WsseUserManipulator::addAllowedIp
	 * @depends testRemoveRole
	 */
	public function testAddAllowedIp($username)
	{
		$this->userManipulator->addAllowedIp($username, '127.0.0.1');
		$user = $this->userProvider->loadUserByUsername($username);
		
		$this->assertEquals(true, $user->hasAllowedIp('127.0.0.1'));
		
		return $username;
	}
	
	/**
	 * @covers WsseUserManipulator::removeAllowedIp
	 * @depends testAddAllowedIp
	 */
	public function testRemoveAllowedIp($username)
	{
		$this->userManipulator->removeAllowedIp($username, '127.0.0.1');
		$user = $this->userProvider->loadUserByUsername($username);
		
		$this->assertEquals(false, $user->hasAllowedIp('127.0.0.1'));
		
		return $username;
	}
	
	/**
	 * @covers WsseUserManipulator::delete
	 * @depends testRemoveAllowedIp
	 * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
	 */
	public function testDelete($username)
	{
		$this->userManipulator->delete($username);
		$this->userProvider->loadUserByUsername($username);
	}
}
