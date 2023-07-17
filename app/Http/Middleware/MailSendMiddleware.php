<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;

class MailSendMiddleware
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

        if ($request->bearerToken() != env('APP_KEY')) {
            return response()->json(['code' => 1,'error' => 'Forbidden'], 200);
        }

        return $next($request);
    }
}
