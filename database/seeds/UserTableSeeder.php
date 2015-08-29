<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Mypleasure\User;
use \DB;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->delete();

        $davy = new User;
        $davy->username = 'davy';
        $davy->password = \Hash::make('azertyuiop');
        $davy->admin = true;
        $davy->save();

        $morgane = new User;
        $morgane->username = 'morgane';
        $morgane->password = \Hash::make('azertyuiop');
        $morgane->save();

        $max = new User;
        $max->username = 'max';
        $max->password = \Hash::make('azertyuiop');
        $max->save();

        $this->command->info('User table seeded!');
    }
}
