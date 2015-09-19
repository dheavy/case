<?php

namespace Mypleasure\Api\V1\Controller;

use Dingo\Api\Exception\UpdateResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Mypleasure\Http\Requests\StoreCollectionRequest;
use Mypleasure\Http\Requests\UpdateCollectionRequest;
use Mypleasure\Http\Requests\DeleteCollectionRequest;
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

    $this->createCollection(
      $request->input('name'),
      (boolean) $request->input('private') || false),
      $user->id
    );

    return response()->json(['status_code' => 201, 'message' => 'Collection successfully created.'], 201);
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
      return response()->json(['status_code' => 200, 'message' => 'Collection successfully updated.'], 200);
    }

    throw new UpdateResourceFailedException('Could not update collection.');
  }

  public function destroy(DeleteCollectionRequest $request, $id)
  {
    $deletableCollection = Collection::find($id);

    // Deletable collection not found.
    if ($deletableCollection === null) {
      throw new DeleteResourceFailedException('Collection (id:' . $id . ') not found.');
    }

    $user = \JWTAuth::parseToken()->toUser();

    // "x-mp-move-to" will be coerced to int(0) if missing.
    // We want an explicit action from the user. A 0 value is too ambiguous.
    // We are looking for a collection ID or -1 or any negative integer value.
    // A negative integer value essentialy means don't transfer videos, destroy them instead.
    $moveVideosHeader = (int) $request->header('x-mp-move-to');

    // If "x-mp-move-to-new" is present, user wants to move all videos to
    // a collection to create from scratch. Value if the header is a string like so:
    // <value_of_name_field>:<value_of_private_field>.
    $moveToNewHeader = $request->header('x-mp-move-to-new');

    if ($moveToNewHeader && is_string($moveToNewHeader)) {
      // Attempt getting serialized values.
      $fields = explode(':', $moveToNewHeader);

      if (count($fields) == 2) {
        $name = $fields[0];
        $private = (bool) $fields[1];

        // Validate.
        $validator = \Validator::make(['name' => $name], [
          'name' => 'required|between:2,30',
        ]);

        if ($validator->fails()) {
          throw new DeleteResourceFailedException('Could not create new collection.');
        }

        // If valid, build new collection instance.
        $this->createCollection($name, $private, $user->id);

        // Get back ID of this new instance.
        $newCollectionId = \DB::table('collections')
                              ->where('user_id', $user->id)
                              ->orderBy('created_at', 'desc')
                              ->first()
                              ->id;

        // Swap videos into new collection.
        $deletableCollection->videos->each(function ($video) use ($newCollectionId) {
          $video->collection_id = $newCollectionId;
          $video->save();
        });
      }
    } else {
      // Too ambiguous.
      if ($moveVideosHeader === 0 && $deletableCollection !== null) {
        throw new DeleteResourceFailedException('Missing an explicit action to take on videos.');
      }

      // User requires videos swapped from one collection to another.
      if ($moveVideosHeader > 0 && $deletableCollection !== null) {
        $receiverCollection = Collection::find($moveVideosHeader);
        // Replacement collection not found.
        if (!$receiverCollection) {
          throw new DeleteResourceFailedException('Replacement collection not found.');
        }

        // Collection of the given ID does not belong to the user!
        if ((int) $receiverCollection->user_id !== (int) $user->id) {
          throw new DeleteResourceFailedException();
        }

        // All good, apply changes.
        $deletableCollection->videos->each(function ($video) use ($receiverCollection) {
          $video->collection_id = $receiverCollection->id;
          $video->save();
        });
      }

      // Delete all videos.
      if ($moveVideosHeader < 0 && $deletableCollection !== null) {
        $deletableCollection->videos->each(function ($video) {
          $video->delete();
        });
      }
    }

    $deletableCollection->delete();
    return response()->json(['status_code' => 205, 'message' => 'Collection successfully deleted.'], 205);
  }

  public function createCollection($name, $private, $userId)
  {
    $collection = new Collection;
    $collection->name = $name;
    $collection->private = $private;
    $collection->user_id = $userId;
    $collection->save();
    return $collection;
  }

}