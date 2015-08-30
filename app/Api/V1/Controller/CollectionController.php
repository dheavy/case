<?php

namespace Mypleasure\Api\V1\Controller;

use Mypleasure\Collection;

class CollectionController extends BaseController {

  public function index()
  {
    return Collection::all();
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