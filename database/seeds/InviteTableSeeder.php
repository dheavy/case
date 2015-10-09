<?php

use Illuminate\Database\Seeder;
use Mypleasure\Invite;
use Mypleasure\User;
use Carbon\Carbon;

class InviteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Deleting Invite table...');
        \DB::table('invites')->delete();

        $this->command->info('Seeding Invite table...');
        $davy = User::where('username', 'davy')->first();
        $morgane = User::where('username', 'morgane')->first();

        $set1 = [
          [
            'email' => 'hello@davybraun.com',
            'code' => 'azertyuiopqsdfghjklmwxcvbn',
            'from_id' => $davy->id,
            'created_at' => Carbon::now()
          ],
          [
            'email' => 'davypeterbraun@gmail.com',
            'code' => 'qsdfghjklwxcvbnazertyuiop',
            'from_id' => $davy->id,
            'created_at' => Carbon::now()
          ]
        ];

        $set2 = [
          [
            'email' => 'morgoune@fraise.com',
            'code' => '1234567890AZERTYUIOP',
            'from_id' => $morgane->id,
            'created_at' => Carbon::now()
          ],
          [
            'email' => 'morgane.mallejac@gmail.com',
            'code' => '0987654321POUYTREZA',
            'from_id' => $morgane->id,
            'created_at' => Carbon::now()
          ]
        ];

        Invite::insert($set1);
        Invite::insert($set2);
    }
}
