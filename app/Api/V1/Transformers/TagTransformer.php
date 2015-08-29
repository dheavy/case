<?php namespace Mypleasure\Api\V1\Transformers;

use Mypleasure\Tag;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract {

  public function transformer(Tag $tag)
  {
    return [
      'id'     => (int) $tag->id,
      'name'   => $tag->name,
      'slug'   => $tag->slug,
      'links'  => [
        'self' => ['rel' => 'self', 'uri' => '/tags/' . $tag->id]
      ]
    ];
  }

}