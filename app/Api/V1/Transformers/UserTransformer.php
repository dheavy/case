<?php namespace Mypleasure\Api\V1\Transformers;

use Mypleasure\User;
use Mypleasure\Collection;
use Mypleasure\Video;
use Mypleasure\Api\V1\Transformers\CollectionTransformer;
use Mypleasure\Api\V1\Transformers\VideoTransformer;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract {

  protected $availableIncludes = [
    'collections',
    'videos'
  ];

  public function transform(User $user)
  {
    return [
      'id'         => (int) $user->id,
      'username'   => $user->username,
      'email'      => $user->email,
      'admin'      => $user->admin,
      'created_at' => $user->created_at,
      'links'      => [
        'self' => ['rel' => 'self', 'uri' => '/users/' . $user->id]
      ]
    ];
  }

  /**
   * Include Collections.
   *
   * @param  User $user
   * @return League\Fractal\Resource\Collection
   */
  public function includeCollections(User $user)
  {
    $collections = $user->collections;
    return $this->collection($collections, new CollectionTransformer);
  }

  /**
   * Include Videos.
   *
   * @param  User $user
   * @return League\Fractal\Resource\Collection
   */
  public function includeVideos(User $user)
  {
    $videos = $user->videos;
    return $this->collection($videos, new VideoTransformer);
  }

}