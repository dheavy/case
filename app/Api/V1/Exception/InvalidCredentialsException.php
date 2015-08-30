<?php

namespace Mypleasure\Api\V1\Exception;

use \Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidCredentialsException extends HttpException {

  /**
     * Create a new invalid credentials exception instance.
     *
     * @param string     $message
     * @param \Exception $previous
     * @param int        $code
     *
     * @return void
     */
    public function __construct($message = null, Exception $previous = null, $code = 0)
    {
        parent::__construct(401, $message ?: 'Invalid credentials.', $previous, [], $code);
    }

}