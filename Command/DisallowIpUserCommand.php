<?php
namespace Oka\ApiBundle\Command;

use Oka\ApiBundle\Util\WsseUserManipulator;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class DisallowIpUserCommand extends AllowedIpCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->setName('oka:api:wsse-user-disallow-ip')
			 ->setDescription('Demotes a user by removing an IP')
			 ->setHelp(<<<EOF
The <info>oka:api:wsse-user-disallow-ip</info> command demotes a user by removing an IP

  <info>php %command.full_name% admin 127.0.0.1</info>
EOF
			 );
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function executeAllowedIpCommand(WsseUserManipulator $manipulator, OutputInterface $output, $username, $ip)
	{
		if ($manipulator->removeAllowedIp($username, $ip)) {
			$output->writeln(sprintf('IP "%s" has been removed to user "%s".', $ip, $username));
		} else {
			$output->writeln(sprintf('User "%s" didn\'t have "%s" IP.', $username, $ip));
		}
	}
}