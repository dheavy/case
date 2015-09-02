<?php

use Illuminate\Database\Seeder;
use Mypleasure\Video;
use Mypleasure\Tag;
use Carbon\Carbon;
use \DB;

class TagTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $this->command->info('Deleting Tag table...');
      DB::table('tags')->delete();

      $this->command->info('Seeding Tag table...');

      $video1 = Video::where('title', 'video de davy 1')->first();
      $video2 = Video::where('title', 'video de davy 2')->first();
      $video3 = Video::where('title', 'video de davy 3')->first();
      $video4 = Video::where('title', 'video de davy 4')->first();
      $video5 = Video::where('title', 'video de davy 5')->first();
      $video6 = Video::where('title', 'video de davy 6')->first();
      $video7 = Video::where('title', 'video de davy 7')->first();
      $video8 = Video::where('title', 'video de davy 8')->first();
      $video9 = Video::where('title', 'video de davy 9')->first();

      $video10 = Video::where('title', 'video de morgane 1')->first();
      $video11 = Video::where('title', 'video de morgane 2')->first();
      $video12 = Video::where('title', 'video de morgane 3')->first();
      $video13 = Video::where('title', 'video de morgane 4')->first();
      $video14 = Video::where('title', 'video de morgane 5')->first();
      $video15 = Video::where('title', 'video de morgane 6')->first();

      $video16 = Video::where('title', 'video de max 1')->first();
      $video17 = Video::where('title', 'video de max 2')->first();
      $video18 = Video::where('title', 'video de max 3')->first();
      $video19 = Video::where('title', 'video de max 4')->first();
      $video20 = Video::where('title', 'video de max 5')->first();
      $video21 = Video::where('title', 'video de max 6')->first();

      $dataTags = [
        ['name' => 'tag 1', 'slug' => 'tag-1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ['name' => 'tag 2', 'slug' => 'tag-2', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ['name' => 'tag 3', 'slug' => 'tag-3', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ['name' => 'tag 4', 'slug' => 'tag-4', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ['name' => 'tag 5', 'slug' => 'tag-5', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
      ];

      Tag::insert($dataTags);

      $tag1 = Tag::where('name', 'tag 1')->first();
      $tag2 = Tag::where('name', 'tag 2')->first();
      $tag3 = Tag::where('name', 'tag 3')->first();
      $tag4 = Tag::where('name', 'tag 4')->first();
      $tag5 = Tag::where('name', 'tag 5')->first();

      $tag1->videos()->attach([
        $video1->id,
        $video2->id,
        $video3->id,
        $video4->id,
        $video5->id
      ]);

      $tag2->videos()->attach([
        $video6->id,
        $video7->id,
        $video8->id,
        $video9->id,
        $video10->id
      ]);

      $tag3->videos()->attach([
        $video11->id,
        $video12->id,
        $video13->id,
        $video14->id,
        $video15->id
      ]);

      $tag4->videos()->attach([
        $video16->id,
        $video17->id,
        $video18->id,
        $video19->id,
        $video20->id
      ]);

      $tag5->videos()->attach([
        $video21->id,
        $video1->id,
        $video2->id,
        $video3->id,
        $video4->id
      ]);
    }
}
