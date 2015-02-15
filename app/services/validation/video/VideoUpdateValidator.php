<?php

namespace Mypleasure\Services\Validation\Video;

use Mypleasure\Services\Validation\AbstractValidator;

/**
 * VideoUpdateValidator is a validator used for Video creation.
 */
class VideoUpdateValidator extends AbstractValidator {

  protected $rules = array(
    'video' => 'required|numeric',
    'title' => 'required|between:2,60'
  );

}