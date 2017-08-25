<?php
namespace Oka\ApiBundle\Command;

use Oka\ApiBundle\Util\WsseUserManipulator;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class DemoteUserCommand extends RoleCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();

		$this->setName('oka:api:wsse-user-demote')
			 ->setDescription('Demote a user by removing a role')
			 ->setHelp(<<<EOF
The <info>oka:api:wsse-user-demote</info> command demotes a user by removing a role

  <info>php %command.full_name% admin ROLE_CUSTOM</info>
EOF
			 );
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function executeRoleCommand(WsseUserManipulator $manipulator, OutputInterface $output, $username, $role)
	{
		if ($manipulator->removeRole($username, $role)) {
			$output->writeln(sprintf('Role "%s" has been removed from user "%s".', $role, $username));
		} else {
			$output->writeln(sprintf('User "%s" didn\'t have "%s" role.', $username, $role));
		}
	}
}