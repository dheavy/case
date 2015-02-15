<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DemoteUserCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'role:demote';

	/**
	 * The username for the User to promote, passed as argument.
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Demotes user from admin to curator.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->username = strtolower($this->argument('username'));
		$user = User::where('username', '=', $this->username)->first();

		if (!$user) {
			$this->info('');
			$this->error("                           ");
			$this->error("  Error: User not found!   ");
			$this->error("                           ");
			$this->info('');
			return;
		}

		if ($user->role->name === 'curator') {
			$this->info('');
			$this->error("                                   ");
			$this->error("  Error: User already an curator!  ");
			$this->error("                                   ");
			$this->info('');
			return;
		}

		if ($user->demote()) {
			$this->info('');
			$this->info("User \"{$this->username}\" was demoted to curator.");
			$this->info('');
			return;
		}

		$this->info('');
		$this->error("                         ");
		$this->error("  Error: no changes...   ");
		$this->error("                         ");
		$this->info('');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('username', InputArgument::REQUIRED, 'The username of the curator to promote.'),
		);
	}

}
