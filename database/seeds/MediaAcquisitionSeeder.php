<?php

use Illuminate\Database\Seeder;
use Mypleasure\User;
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
      $this->command->info('Deleting Video Store Queue...');
      $this->db()->delete();
      $this->command->info('Seeding Video Store Queue...');

      $davy = User::where('username', 'davy')->first();
      $morgane = User::where('username', 'morgane')->first();

      $this->db()->insert([
        [
          'hash' => md5('https://www.youtube.com/watch?v=7WRFUXyVZoQ'),
          'url' => 'https://www.youtube.com/watch?v=7WRFUXyVZoQ',
          'requester' => $davy->id,
          'status' => 'ready'
        ],
        [
          'hash' => md5('https://www.youtube.com/watch?v=tAJqVfu6AqA'),
          'url' => 'https://www.youtube.com/watch?v=tAJqVfu6AqA',
          'requester' => $davy->id,
          'status' => 'ready'
        ],
        [
          'hash' => md5('https://www.youtube.com/watch?v=ZK4_O7QJ55Y'),
          'url' => 'https://www.youtube.com/watch?v=ZK4_O7QJ55Y',
          'requester' => $morgane->id,
          'status' => 'ready'
        ],
        [
          'hash' => md5('https://www.youtube.com/watch?v=5OGTiU8AT98'),
          'url' => 'https://www.youtube.com/watch?v=5OGTiU8AT98',
          'requester' => $morgane->id,
          'status' => 'done'
        ],
      ]);
    }

    protected function db()
    {
      return DB::connection('mongodb')->collection('queue');
    }
}
