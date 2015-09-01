<?php

namespace Mypleasure\Api\V1\Controller;

use Dingo\Api\Exception\UpdateResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Mypleasure\Api\V1\Transformer\VideoTransformer;
// use Mypleasure\Http\Requests\StoreVideoRequest;
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

  }

  public function update($id)
  {

  }

  public function destroy($id)
  {

  }

}