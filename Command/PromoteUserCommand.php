<?php
namespace Oka\ApiBundle\Command;

use Oka\ApiBundle\Util\WsseUserManipulator;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class PromoteUserCommand extends RoleCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->setName('oka:api:wsse-user-promote')
			 ->setDescription('Promotes a user by adding a role')
			 ->setHelp(<<<EOF
The <info>oka:api:wsse-user-promote</info> command promotes a user by adding a role

  <info>php %command.full_name% admin ROLE_CUSTOM</info>
EOF
			 );
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function executeRoleCommand(WsseUserManipulator $manipulator, OutputInterface $output, $username, $role)
	{
		if ($manipulator->addRole($username, $role)) {
			$output->writeln(sprintf('Role "%s" has been added to user "%s".', $role, $username));
		} else {
			$output->writeln(sprintf('User "%s" did already have "%s" role.', $username, $role));
		}
	}
}