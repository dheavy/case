<?php

namespace Mypleasure\Services\Validation\Video;

use Mypleasure\Services\Validation\AbstractValidator;

/**
 * VideoCreateValidator is a validator used for Video creation.
 */
class VideoCreateValidator extends AbstractValidator {

  protected $rules = array(
    'url' => 'required|url',
    'collectionId' => 'required|numeric',
    'collectionName' => 'required|between:2,30|sometimes'
  );

}