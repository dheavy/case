<?php namespace Mypleasure\Api\V1\Transformer;

use Mypleasure\Video;
use League\Fractal\TransformerAbstract;

class VideoTransformer extends TransformerAbstract {

  public function transform(Video $video)
  {
    return [
      'id'            => (int) $video->id,
      'hash'          => $video->hash,
      'collection_id' => $video->collection_id,
      'title'         => $video->title,
      'slug'          => $video->slug,
      'poster'        => $video->poster,
      'original_url'  => $video->original_url,
      'embed_url'     => $video->embed_url,
      'duration'      => $video->duration,
      'created_at'    => $video->created_at,
      'links' => [
        'self'        => ['rel' => 'self', 'uri' => '/videos/' . $video->id],
        'collection'  => ['rel' => 'collection', 'uri' => '/collections/' . $video->collection_id]
      ]
    ];
  }

}