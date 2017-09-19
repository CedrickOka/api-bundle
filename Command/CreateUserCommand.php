<?php
namespace Oka\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class CreateUserCommand extends ContainerAwareCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this->setName('oka:api:wsse-user-create')
			 ->setDescription('Create a user.')
			 ->setDefinition([
			 		new InputArgument('username', InputArgument::REQUIRED, 'The username'),
			 		new InputArgument('password', InputArgument::REQUIRED, 'The password'),
			 		new InputOption('inactive', null, InputOption::VALUE_NONE, 'Set the user as inactive')
			 ])
			 ->setHelp(<<<EOF
The <info>oka:api:wsse-user-create</info> command creates a user:

  <info>php %command.full_name% admin</info>

This interactive shell will ask you for a password.

You can alternatively specify the password as the second arguments:

  <info>php %command.full_name% admin mypassword</info>

You can create an inactive user (will not be able to log in):

  <info>php %command.full_name% admin --inactive</info>
EOF
			 );
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
		
		if (!$input->getArgument('password')) {
			$question = new Question('Please choose a password:');
			$question->setValidator(function ($password) {
				if (empty($password)) {
					throw new \Exception('Password can not be empty');
				}
				
				return $password;
			});
			$question->setHidden(true);
			$questions['password'] = $question;
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
		$password = $input->getArgument('password');
		$inactive = $input->getOption('inactive');
		
		$manipulator = $this->getContainer()->get('oka_api.util.wsse_user_manipulator');
		$manipulator->create($username, $password, !$inactive);
		
		$output->writeln(sprintf('Created user <comment>%s</comment>', $username));
	}
}
