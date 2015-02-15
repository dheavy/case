<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class PromoteUserCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'role:promote';

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
	protected $description = 'Promotes user from curator to admin role.';

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
	 * @return void
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

		if ($user->role->name === 'admin') {
			$this->info('');
			$this->error("                                  ");
			$this->error("  Error: User already an admin!   ");
			$this->error("                                  ");
			$this->info('');
			return;
		}

		if ($user->promote()) {
			$this->info('');
			$this->info("User \"{$this->username}\" was promoted to admin.");
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
