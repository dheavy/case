<?php

namespace Mypleasure\Api\V1\Controller;

use Dingo\Api\Exception\DeleteResourceFailedException;
use Mypleasure\Http\Requests\StoreTagRequest;
use Mypleasure\Http\Requests\DeleteTagRequest;
use Mypleasure\Api\V1\Transformer\TagTransformer;
use Mypleasure\Tag;

class TagController extends BaseController {

  public function __construct()
  {
    $this->middleware('api.auth');
  }

  public function index()
  {
    $tags = Tag::all();
    return $this->response->collection($tags, new TagTransformer);
  }

  public function store(StoreTagRequest $request)
  {
    $tag = new Tag;
    $tag->name = $request->input('name');
    $tag->slugifyName();
    $tag->save();

    return response()->json(['status_code' => 200, 'message' => 'Tag successfully created.'], 200);
  }

  public function show($id)
  {
    $tag = Tag::find($id);
    if (!$tag) {
      return $this->response->errorNotFound();
    }
    return $this->item($tag, new TagTransformer);
  }

  public function destroy(DeleteTagRequest $request, $id)
  {

  }

}