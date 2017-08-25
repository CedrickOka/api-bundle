<?php
namespace Oka\ApiBundle\Command;

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
class ActivateUserCommand extends ContainerAwareCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this->setName('oka:api:wsse-user-activate')
			 ->setDescription('Activate a user')
			 ->setDefinition([
			 		new InputArgument('username', InputArgument::REQUIRED, 'The username')
			 ])
			->setHelp(<<<EOF
The <info>oka:api:wsse-user-activate</info> command activates a user (so they will be able to log in):

  <info>php %command.full_name% admin</info>
EOF
			);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function interact(InputInterface $input, OutputInterface $output)
	{
		if (!$input->getArgument('username')) {
			$question = new Question('Please choose a username:');
			$question->setValidator(function ($username) {
				if (empty($username)) {
					throw new \Exception('Username can not be empty');
				}
				
				return $username;
			});
			
			$answer = $this->getHelper('question')->ask($input, $output, $question);
			$input->setArgument('username', $answer);
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$username = $input->getArgument('username');
		
		$manipulator = $this->getContainer()->get('oka_api.util.wsse_user_manipulator');
		$manipulator->activate($username);
		
		$output->writeln(sprintf('User "%s" has been activated.', $username));
	}
}