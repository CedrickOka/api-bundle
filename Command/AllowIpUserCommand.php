<?php
namespace Oka\ApiBundle\Command;

use Oka\ApiBundle\Util\WsseUserManipulator;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class AllowIpUserCommand extends AllowedIpCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->setName('oka:api:wsse-user-allow-ip')
			 ->setDescription('Promotes a user by adding an IP')
			 ->setHelp(<<<EOF
The <info>oka:api:wsse-user-allow-ip</info> command promotes a user by adding an IP

  <info>php %command.full_name% admin 127.0.0.1</info>
EOF
			 );
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function executeAllowedIpCommand(WsseUserManipulator $manipulator, OutputInterface $output, $username, $ip)
	{
		if ($manipulator->addAllowedIp($username, $ip)) {
			$output->writeln(sprintf('IP "%s" has been added to user "%s".', $ip, $username));
		} else {
			$output->writeln(sprintf('User "%s" did already have "%s" IP.', $username, $ip));
		}
	}
}