<?php

use Illuminate\Database\Seeder;
use Mypleasure\Collection as Collection;
use Mypleasure\User;
use Carbon\Carbon;
use \DB;

class MediaAcquisitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $this->command->info('Deleting Video Queue...');
      $this->db('queue')->delete();

      $this->command->info('Deleting Video Store...');
      $this->db('videos')->delete();

      $this->command->info('Seeding Video Store and Queue...');

      $davy = User::where('username', 'davy')->first();
      $morgane = User::where('username', 'morgane')->first();

      $this->db('queue')->insert([
        [
          'hash' => md5('https://www.youtube.com/watch?v=7WRFUXyVZoQ'),
          'url' => 'https://www.youtube.com/watch?v=7WRFUXyVZoQ',
          'requester' => $davy->id,
          'collection' => $davy->collections[0]->id,
          'status' => 'ready'
        ],
        [
          'hash' => md5('https://www.youtube.com/watch?v=tAJqVfu6AqA'),
          'url' => 'https://www.youtube.com/watch?v=tAJqVfu6AqA',
          'requester' => $davy->id,
          'collection' => $davy->collections[0]->id,
          'status' => 'pending'
        ],
        [
          'hash' => md5('https://www.youtube.com/watch?v=ZK4_O7QJ55Y'),
          'url' => 'https://www.youtube.com/watch?v=ZK4_O7QJ55Y',
          'requester' => $morgane->id,
          'collection' => $morgane->collections[0]->id,
          'status' => 'ready'
        ],
        [
          'hash' => md5('https://www.youtube.com/watch?v=5OGTiU8AT98'),
          'url' => 'https://www.youtube.com/watch?v=5OGTiU8AT98',
          'requester' => $morgane->id,
          'collection' => $morgane->collections[0]->id,
          'status' => 'done'
        ],
      ]);

      $this->db('videos')->insert([
        [
          'original_url' => 'https://www.youtube.com/watch?v=7WRFUXyVZoQ',
          'poster' => 'http://img.youtube.com/vi/7WRFUXyVZoQ/mqdefault.jpg',
          'created_at' => Carbon::now(),
          'hash' => md5('https://www.youtube.com/watch?v=7WRFUXyVZoQ'),
          'duration' => '00:02:36',
          'embed_url' => 'https://www.youtube.com/embed/7WRFUXyVZoQ',
          'title' => 'title for https://www.youtube.com/watch?v=7WRFUXyVZoQ',
          'naughty' => false
        ],
        [
          'original_url' => 'https://www.youtube.com/watch?v=tAJqVfu6AqA',
          'poster' => 'http://img.youtube.com/vi/tAJqVfu6AqA/mqdefault.jpg',
          'created_at' => Carbon::now(),
          'hash' => md5('https://www.youtube.com/watch?v=tAJqVfu6AqA'),
          'duration' => '00:02:36',
          'embed_url' => 'https://www.youtube.com/embed/tAJqVfu6AqA',
          'title' => 'title for https://www.youtube.com/watch?v=tAJqVfu6AqA',
          'naughty' => false
        ],
        [
          'original_url' => 'https://www.youtube.com/watch?v=ZK4_O7QJ55Y',
          'poster' => 'http://img.youtube.com/vi/ZK4_O7QJ55Y/mqdefault.jpg',
          'created_at' => Carbon::now(),
          'hash' => md5('https://www.youtube.com/watch?v=ZK4_O7QJ55Y'),
          'duration' => '00:02:36',
          'embed_url' => 'https://www.youtube.com/embed/ZK4_O7QJ55Y',
          'title' => 'title for https://www.youtube.com/watch?v=ZK4_O7QJ55Y',
          'naughty' => false
        ]
      ]);
    }

    protected function db($collection)
    {
      return DB::connection('mongodb')->collection($collection);
    }
}
