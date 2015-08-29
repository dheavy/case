<?php namespace Mypleasure\Api\V1\Transformers;

use Mypleasure\Collection;
use Mypleasure\Video;
use Mypleasure\Api\V1\Transformers\VideoTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Transformer for Collection model.
 */
class CollectionTransformer extends TransformerAbstract {

  protected $availableIncludes = [
    'videos'
  ];

  public function transform(Collection $collection)
  {
    return [
      'id'         => (int) $collection->id,
      'name'       => $collection->name,
      'slug'       => $collection->slug,
      'user_id'    => $collection->user_id,
      'created_at' => $collection->created_at,
      'links'      => [
        'self' => ['rel' => 'self', 'uri' => '/collections/' . $collection->id]
      ]
    ];
  }

  public function includeVideos(Collection $collection)
  {
    $videos = $collections->videos;
    return $this->collection($videos, new VideoTransformer);
  }

}