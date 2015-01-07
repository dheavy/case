<?php

namespace Mypleasure\Services\Validation;

use Illuminate\Validation\Factory as Validator;

/**
 * AbstractValidator is the abstract class all validator classes must subclass.
 */

abstract class AbstractValidator implements ValidableInterface {

  /**
   * The validator factored by Laravel.
   *
   * @var \Illuminate\Validation\Factory
   */
  protected $validator;

  /**
   * Input from user.
   *
   * @var array
   */
  protected $data = array();

  /**
   * Possible errors after validation attempt.
   *
   * @var array
   */
  protected $errors = array();

  /**
   * Validation rules.
   *
   * @var array
   */
  protected $rules = array();

  /**
   * Possible error messages after validation attempt.
   *
   * @var array
   */
  protected $messages = array();

  /**
   * @param Validator $validator A instance of the validator factored by Laravel.
   */
  public function __construct(Validator $validator)
  {
    $this->validator = $validator;
  }

  /**
   * Adds user input.
   * @param  array  $data       The input from user form.
   * @return AbstractValidator  The instance of validator.
   */
  public function with(array $data)
  {
    $this->data = $data;
    return $this;
  }

  /**
   * Attempts validating input.
   * Will only try to validate from given keys, if they actually exists.
   * So it's not mandatory to recreate the full set of input each time,
   * i.e. you can only validate one field if need be (useful for updates).
   *
   * @param  boolean $ignoreNulls If true, bypass validation for null or non-existing input.
   *                              Caution: use it for updates, not creations.
   * @return boolean True if passes, false otherwise.
   */
  public function passes($ignoreNulls = false)
  {
    $data = array();
    $rules = array();

    foreach ($this->data as $key => $value) {
      if (array_key_exists($key, $this->rules)) {
        if ($ignoreNulls && is_null($value)) continue;
        $data[$key] = $this->data[$key];
        $rules[$key] = $this->rules[$key];
      }
    }

    $validator = $this->validator->make(
      $data,
      $rules,
      $this->messages
    );

    if ($validator->fails()) {
      $this->errors = $validator->messages();
      return false;
    }

    return true;
  }

  /**
   * @return array The possible array of error messages from the validation.
   */
  public function errors()
  {
    return $this->errors;
  }

}
