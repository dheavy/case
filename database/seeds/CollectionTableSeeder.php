<?php

use Illuminate\Database\Seeder;
use Mypleasure\Collection;
use Mypleasure\User;
use Carbon\Carbon;

class CollectionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $this->command->info('Deleting Collection table...');
      \DB::table('collections')->delete();

      $this->command->info('Seeding Collection table...');
      $davy = User::where('username', 'davy')->first();
      $morgane = User::where('username', 'morgane')->first();
      $max = User::where('username', 'max')->first();

      $this->createCollection('collection de davy 1', false, $davy->id);
      $this->createCollection('collection de davy 2', false, $davy->id);
      $this->createCollection('collection de davy 3', true, $davy->id);

      $this->createCollection('collection de morgane 1', false, $morgane->id);
      $this->createCollection('collection de morgane 2', true, $morgane->id);

      $this->createCollection('collection de max 1', false, $max->id);
      $this->createCollection('collection de max 2', true, $max->id);
    }

    protected function createCollection($name, $private, $userId)
    {
      $collection = new Collection;
      $collection->name = $name;
      $collection->slugifyName();
      $collection->private = $private;
      $collection->user_id = $userId;
      $collection->save();
      return $collection;
    }
}
