<?php

namespace Mypleasure\Events;

class JWTEvents extends Event {

    public function notFound()
    {
        return response()->json(['message' => 'Token Not Found.', 'status_code' => 401], 401);
    }

    public function invalid()
    {
        return response()->json(['message' => 'Token Invalid.', 'status_code' => 401], 401);
    }

    public function expired()
    {
        return response()->json(['message' => 'Token expired.', 'status_code' => 401], 401);
    }

    public function missing()
    {
        return response()->json(['message' => 'Token Missing.', 'status_code' => 401], 401);
    }
}