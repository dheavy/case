<?php

namespace Mypleasure\Api\V1\Controller;

use Dingo\Api\Exception\UpdateResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Mypleasure\Api\V1\Transformer\VideoTransformer;
use Mypleasure\Http\Requests\StoreVideoRequest;
// use Mypleasure\Http\Requests\UpdateVideoRequest;
// use Mypleasure\Http\Requests\DeleteVideoRequest;
use Illuminate\Http\Request;
use Mypleasure\Video;

class VideoController extends BaseController {

  public function __construct()
  {
    $this->middleware('api.auth');
  }

  public function index()
  {
    $user = \JWTAuth::parseToken()->toUser();
    $videos = $user->videos;
    return $this->response->collection($videos, new VideoTransformer);
  }

  public function show($id)
  {
    $user = \JWTAuth::parseToken()->toUser();
    $video = Video::find($id);

    if (!$video) {
      return $this->response->errorNotFound();
    }
    if ($video && (int) $video->getOwner()->id === $user->id) {
      return $this->item($video, new VideoTransformer);
    }
    return $this->response->errorForbidden();
  }

  public function store(StoreVideoRequest $request)
  {
    $user = \JWTAuth::parseToken()->toUser();

    $hash = $request->input('hash');
    $collectionId = $request->input('collection_id');
    $title = $request->input('title');
    $originalUrl = $request->input('original_url');
    $embedUrl = $request->input('embed_url');
    $duration = $request->input('duration');
    $naughty = (bool) $request->input('naughty') || false;

    $video = new Video;
    $video->hash = $hash;
    $video->title = $title;
    $video->slugifyTitle();
    $video->collection_id = $collectionId;
    $video->original_url = $originalUrl;
    $video->embed_url = $embedUrl;
    $video->duration = $duration;
    $video->naughty = $naughty;
    $video->save();

    return response()->json(['status_code' => 200, 'message' => 'Video successfully created.']);
  }

  public function update($id)
  {

  }

  public function destroy($id)
  {

  }

}