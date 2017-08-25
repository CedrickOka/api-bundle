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
abstract class RoleCommand extends ContainerAwareCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this->setDefinition([
				new InputArgument('username', InputArgument::REQUIRED, 'The username'),
				new InputArgument('role', InputArgument::REQUIRED, 'The role')
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
		
		if (!$input->getArgument('role')) {
			$question = new Question('Please choose a role:');
			$question->setValidator(function ($role) {
				if (empty($role)) {
					throw new \Exception('Role can not be empty');
				}
				
				return $role;
			});
			$questions['role'] = $question;
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
		$role = $input->getArgument('role');
		
		$manipulator = $this->getContainer()->get('oka_api.util.wsse_user_manipulator');
		$this->executeRoleCommand($manipulator, $output, $username, $role);
	}

	/**
	 * @see Command
	 *
	 * @param WsseUserManipulator $manipulator
	 * @param OutputInterface $output
	 * @param string		  $username
	 * @param string		  $role
	 */
	abstract protected function executeRoleCommand(WsseUserManipulator $manipulator, OutputInterface $output, $username, $role);
}