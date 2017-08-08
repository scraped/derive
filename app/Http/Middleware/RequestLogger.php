<?php

namespace App\Http\Middleware;

use Closure;
use Log;

class RequestLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        Log::info('requests', [
            'request' => $request->all(),
            'header' => $request->headers->all()
        ]);
    }
}
