<?php

namespace App;

class Model
{
    protected static $fbToken;
    protected static $fbUserId;

    public static function getFbToken()
    {
        if (empty(static::$fbToken)) {
            static::$fbToken = env('GRAPH_API_CLIENT_ID') . '|' . env('GRAPH_API_SECRET');
        }
        return static::$fbToken;
    }

    public static function setFbToken($token)
    {
        static::$fbToken = $token;
    }

    public static function getFbUserId()
    {
        return self::$fbUserId;
    }

    public static function setFbUserId($fbUserId)
    {
        self::$fbUserId = $fbUserId;
    }
}