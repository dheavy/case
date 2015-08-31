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
    return $this->collection($collections, new CollectionTransformer);
  }

  public function show($id)
  {

  }

  public function update($id)
  {

  }

  public function destroy($id)
  {

  }

}