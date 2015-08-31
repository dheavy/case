<?php

namespace Mypleasure\Api\V1\Controller;

use Dingo\Api\Exception\UpdateResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Mypleasure\Http\Requests\StoreCollectionRequest;
use Mypleasure\Http\Requests\UpdateCollectionRequest;
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

  public function store(StoreCollectionRequest $request)
  {
    $user = \JWTAuth::parseToken()->toUser();
    $collection = new Collection;
    $collection->name = $request->input('name');
    $collection->private = (boolean) $request->input('private') || false;
    $collection->user_id = $user->id;
    $collection->save();

    return response()->json(['status_code' => 200, 'message' => 'Collection successfully created']);
  }

  public function update(UpdateCollectionRequest $request, $id)
  {
    $user = \JWTAuth::parseToken()->toUser();
    $collection = Collection::find($id);

    if ($collection !== null) {
      if ($request->input('name')) {
        $collection->name = $request->input('name');
        $collection->slugifyName();
      }

      if ($request->input('private')) {
        $collection->private = $request->input('private');
      }

      $collection->save();
      return response()->json(['status_code' => 200, 'message' => 'Collection successfully edited.']);
    }

    throw new UpdateResourceFailedException('Could not update collection.');
  }

  public function destroy($id)
  {

  }

}