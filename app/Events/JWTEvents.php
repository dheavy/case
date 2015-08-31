<?php

namespace Mypleasure\Events;

class JWTEvents extends Event {

    public function notFound()
    {
        return response()->json(['error' => 'Token Not Found.'], 401);
    }

    public function invalid()
    {
        return response()->json(['error' => 'Token Invalid.'], 401);
    }

    public function expired()
    {
        return response()->json(['error' => 'Token expired.'], 401);
    }

    public function missing()
    {
        return response()->json(['error' => 'Token Missing.'], 401);
    }
}