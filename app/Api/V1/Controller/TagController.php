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

  }

  public function store()
  {

  }

  public function show($id)
  {

  }

  public function destroy($id)
  {

  }

}