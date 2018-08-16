<?php
namespace Oka\ApiBundle\Command;

use Oka\ApiBundle\Util\WsseUserManipulator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
abstract class AllowedIpCommand extends ContainerAwareCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this->setDefinition([
				new InputArgument('username', InputArgument::REQUIRED, 'The username'),
				new InputArgument('ip', InputArgument::REQUIRED, 'The IP')
		]);
	}
	/**
	 * {@inheritdoc}
	 */
	protected function interact(InputInterface $input, OutputInterface $output)
	{
		$questions = [];

		if (!$input->getArgument('username')) {
			$question = new Question('Please choose a username:');
			$question->setValidator(function ($username) {
				if (empty($username)) {
					throw new \Exception('Username can not be empty');
				}
				
				return $username;
			});
			$questions['username'] = $question;
		}
		
		if (!$input->getArgument('ip')) {
			$question = new Question('Please choose a IP:');
			$question->setValidator(function ($ip) {
				if (empty($ip)) {
					throw new \Exception('IP can not be empty');
				}
				
				return $ip;
			});
			$questions['ip'] = $question;
		}
		
		foreach ($questions as $name => $question) {
			$answer = $this->getHelper('question')->ask($input, $output, $question);
			$input->setArgument($name, $answer);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$username = $input->getArgument('username');
		$ip = $input->getArgument('ip');
		
		$manipulator = $this->getContainer()->get('oka_api.util.wsse_user_manipulator');
		$this->executeAllowedIpCommand($manipulator, $output, $username, $ip);
	}

	/**
	 * @param WsseUserManipulator $manipulator
	 * @param OutputInterface $output
	 * @param string		  $username
	 * @param string		  $ip
	 */
	abstract protected function executeAllowedIpCommand(WsseUserManipulator $manipulator, OutputInterface $output, $username, $ip);
}
