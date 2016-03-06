<?php

namespace Libcast\AssetDistributor;

use Symfony\Component\HttpFoundation\Request as HttpRequest;

class Request
{
    /**
     *
     * @var HttpRequest
     */
    protected static $request;

    /**
     *
     * @return HttpRequest
     */
    public static function get()
    {
        if (!self::$request) {
            self::$request = HttpRequest::createFromGlobals();
        }

        return self::$request;
    }
}
