<?php

namespace Mypleasure\Api\V1\Controller;

use Mypleasure\Api\V1\Transformer\CollectionTransformer;
use Mypleasure\Collection;

class CollectionController extends BaseController {

  public function __construct()
  {
    $this->middleware('api.auth');
  }

  public function index()
  {
    $user = \JWTAuth::parseToken()->toUser();
    $collections = $user->collections;
    return $this->response->collection($collections, new CollectionTransformer);
  }

  public function show($id)
  {
    $user = \JWTAuth::parseToken()->toUser();
    $collection = Collection::find($id);
    if (!$collection) {
      return $this->response->errorNotFound();
    }
    if ($collection && (int) $collection->user_id === $user->id) {
      return $this->item($collection, new CollectionTransformer);
    }
    return $this->response->errorForbidden();
  }

  public function update($id)
  {

  }

  public function destroy($id)
  {

  }

}