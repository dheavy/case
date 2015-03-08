<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Mypleasure\Services\Url\UrlSanitizer;

class UpdateNsfwFieldCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nsfw:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Ensure no video instance or in store has a SFW/NSFW status.';

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
		$this->updateMongo();
		$this->updatePostgres();
		$this->info('');
		$this->info("         ");
		$this->info("  Done ! ");
		$this->info("         ");
		$this->info('');
	}

	/**
	 * Update entries in videostore.
	 *
	 * @return void
	 */
	protected function updateMongo()
	{
		$toUpdate = DB::connection('mongodb')
			->collection('videos')
			->where('nsfw', '=', null)
			->get();

		foreach ($toUpdate as $entry) {
			$nsfw = true;
			$originalUrl = $entry['original_url'];
			$origin = App::make('UrlSanitizer')->getDomain($originalUrl);

			if (in_array($origin, Config::get('viewmode.sfw'))) {
				$nsfw = false;
			}

			DB::connection('mongodb')
				->collection('videos')
				->where('nsfw', '=', null)
				->where('original_url', '=', $originalUrl)
				->update(array('nsfw' => $nsfw));
		}
	}

	/**
	 * Update entries in Postgres (site).
	 *
	 * @return void
	 */
	protected function updatePostgres()
	{
		$videos = Video::all();
		$videos->each(function ($video) {
			$nsfw = 1;
			$originalUrl = $video->original_url;
			$origin = App::make('UrlSanitizer')->getDomain($originalUrl);

			if (in_array($origin, Config::get('viewmode.sfw'))) {
				$nsfw = 0;
			}

			$video->nsfw = $nsfw;
			$video->save();
		});
	}

}
