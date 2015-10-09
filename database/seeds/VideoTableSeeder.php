<?php

use Illuminate\Database\Seeder;
use Mypleasure\Collection;
use Mypleasure\Video;
use Carbon\Carbon;

class VideoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $this->command->info('Deleting Video table...');
      \DB::table('videos')->delete();

      $this->command->info('Seeding Video table...');

      $collectionDavy1 = Collection::where('name', 'collection de davy 1')->first();
      $collectionDavy2 = Collection::where('name', 'collection de davy 2')->first();
      $collectionDavy3 = Collection::where('name', 'collection de davy 3')->first();

      $collectionMorgane1 = Collection::where('name', 'collection de morgane 1')->first();
      $collectionMorgane2 = Collection::where('name', 'collection de morgane 2')->first();

      $collectionMax1 = Collection::where('name', 'collection de max 1')->first();
      $collectionMax2 = Collection::where('name', 'collection de max 2')->first();

      $setDavy1 = [
        [
          'hash' => 'hashdavy1',
          'collection_id' => $collectionDavy1->id,
          'title' => 'video de davy 1',
          'slug' => 'video-de-davy-1',
          'poster' => 'posterdavy1',
          'original_url' => 'originalurldavy1',
          'embed_url' => 'embedurldavy1',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashdavy2',
          'collection_id' => $collectionDavy1->id,
          'title' => 'video de davy 2',
          'slug' => 'video-de-davy-2',
          'poster' => 'posterdavy2',
          'original_url' => 'originalurldavy2',
          'embed_url' => 'embedurldavy2',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashdavy3',
          'collection_id' => $collectionDavy1->id,
          'title' => 'video de davy 3',
          'slug' => 'video-de-davy-3',
          'poster' => 'posterdavy3',
          'original_url' => 'originalurldavy3',
          'embed_url' => 'embedurldavy3',
          'duration' => '--:--:--',
          'naughty' => true,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]
      ];

      $setDavy2 = [
        [
          'hash' => 'hashdavy4',
          'collection_id' => $collectionDavy2->id,
          'title' => 'video de davy 4',
          'slug' => 'video-de-davy-4',
          'poster' => 'posterdavy4',
          'original_url' => 'originalurldavy4',
          'embed_url' => 'embedurldavy4',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashdavy5',
          'collection_id' => $collectionDavy2->id,
          'title' => 'video de davy 5',
          'slug' => 'video-de-davy-5',
          'poster' => 'posterdavy5',
          'original_url' => 'originalurldavy5',
          'embed_url' => 'embedurldavy5',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashdavy6',
          'collection_id' => $collectionDavy2->id,
          'title' => 'video de davy 6',
          'slug' => 'video-de-davy-6',
          'poster' => 'posterdavy6',
          'original_url' => 'originalurldavy6',
          'embed_url' => 'embedurldavy6',
          'duration' => '--:--:--',
          'naughty' => true,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]
      ];

      $setDavy3 = [
        [
          'hash' => 'hashdavy7',
          'collection_id' => $collectionDavy3->id,
          'title' => 'video de davy 7',
          'slug' => 'video-de-davy-7',
          'poster' => 'posterdavy7',
          'original_url' => 'originalurldavy7',
          'embed_url' => 'embedurldavy7',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashdavy8',
          'collection_id' => $collectionDavy3->id,
          'title' => 'video de davy 8',
          'slug' => 'video-de-davy-8',
          'poster' => 'posterdavy8',
          'original_url' => 'originalurldavy8',
          'embed_url' => 'embedurldavy8',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashdavy9',
          'collection_id' => $collectionDavy3->id,
          'title' => 'video de davy 9',
          'slug' => 'video-de-davy-9',
          'poster' => 'posterdavy9',
          'original_url' => 'originalurldavy9',
          'embed_url' => 'embedurldavy9',
          'duration' => '--:--:--',
          'naughty' => true,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]
      ];

      $setMorgane1 = [
        [
          'hash' => 'hashmorgane1',
          'collection_id' => $collectionMorgane1->id,
          'title' => 'video de morgane 1',
          'slug' => 'video-de-morgane-1',
          'poster' => 'postermorgane1',
          'original_url' => 'originalurlmorgane1',
          'embed_url' => 'embedurlmorgane1',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashmorgane2',
          'collection_id' => $collectionMorgane1->id,
          'title' => 'video de morgane 2',
          'slug' => 'video-de-morgane-2',
          'poster' => 'postermorgane2',
          'original_url' => 'originalurlmorgane2',
          'embed_url' => 'embedurlmorgane2',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashmorgane3',
          'collection_id' => $collectionMorgane1->id,
          'title' => 'video de morgane 3',
          'slug' => 'video-de-morgane-3',
          'poster' => 'postermorgane3',
          'original_url' => 'originalurlmorgane3',
          'embed_url' => 'embedurlmorgane3',
          'duration' => '--:--:--',
          'naughty' => true,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]
      ];

      $setMorgane2 = [
        [
          'hash' => 'hashmorgane4',
          'collection_id' => $collectionMorgane2->id,
          'title' => 'video de morgane 4',
          'slug' => 'video-de-morgane-4',
          'poster' => 'postermorgane4',
          'original_url' => 'originalurlmorgane4',
          'embed_url' => 'embedurlmorgane4',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashmorgane5',
          'collection_id' => $collectionMorgane2->id,
          'title' => 'video de morgane 5',
          'slug' => 'video-de-morgane-5',
          'poster' => 'postermorgane5',
          'original_url' => 'originalurlmorgane5',
          'embed_url' => 'embedurlmorgane5',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashmorgane6',
          'collection_id' => $collectionMorgane2->id,
          'title' => 'video de morgane 6',
          'slug' => 'video-de-morgane-6',
          'poster' => 'postermorgane6',
          'original_url' => 'originalurlmorgane6',
          'embed_url' => 'embedurlmorgane6',
          'duration' => '--:--:--',
          'naughty' => true,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]
      ];

      $setMax1 = [
        [
          'hash' => 'hashmax1',
          'collection_id' => $collectionMax1->id,
          'title' => 'video de max 1',
          'slug' => 'video-de-max-1',
          'poster' => 'postermax1',
          'original_url' => 'originalurlmax1',
          'embed_url' => 'embedurlmax1',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashmax2',
          'collection_id' => $collectionMax1->id,
          'title' => 'video de max 2',
          'slug' => 'video-de-max-2',
          'poster' => 'postermax2',
          'original_url' => 'originalurlmax2',
          'embed_url' => 'embedurlmax2',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashmax3',
          'collection_id' => $collectionMax1->id,
          'title' => 'video de max 3',
          'slug' => 'video-de-max-3',
          'poster' => 'postermax3',
          'original_url' => 'originalurlmax3',
          'embed_url' => 'embedurlmax3',
          'duration' => '--:--:--',
          'naughty' => true,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]
      ];

      $setMax2 = [
        [
          'hash' => 'hashmax4',
          'collection_id' => $collectionMax2->id,
          'title' => 'video de max 4',
          'slug' => 'video-de-max-4',
          'poster' => 'postermax4',
          'original_url' => 'originalurlmax4',
          'embed_url' => 'embedurlmax4',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashmax5',
          'collection_id' => $collectionMax2->id,
          'title' => 'video de max 5',
          'slug' => 'video-de-max-5',
          'poster' => 'postermax5',
          'original_url' => 'originalurlmax5',
          'embed_url' => 'embedurlmax5',
          'duration' => '--:--:--',
          'naughty' => false,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ],
        [
          'hash' => 'hashmax6',
          'collection_id' => $collectionMax2->id,
          'title' => 'video de max 6',
          'slug' => 'video-de-max-6',
          'poster' => 'postermax6',
          'original_url' => 'originalurlmax6',
          'embed_url' => 'embedurlmax6',
          'duration' => '--:--:--',
          'naughty' => true,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]
      ];

      Video::insert($setDavy1);
      Video::insert($setDavy2);
      Video::insert($setDavy3);
      Video::insert($setMorgane1);
      Video::insert($setMorgane2);
      Video::insert($setMax1);
      Video::insert($setMax2);
    }
}
