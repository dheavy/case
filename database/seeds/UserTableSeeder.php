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
        DB::table('users')->truncate();

        $data = [
          ['username' => 'davy', 'email' => 'davy@mypleasu.re', 'password' => 'azertyuiop', 'admin' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now() ],
          ['username' => 'morgane', 'email' => 'morgane@mypleasu.re', 'password' => 'azertyuiop', 'admin' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now() ],
          ['username' => 'max', 'email' => 'max@mypleasu.re', 'password' => 'azertyuiop', 'admin' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now() ]
        ];

        User::insert($data);
    }
}
