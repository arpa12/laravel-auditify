<?php

namespace Auditify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auditify\Facades\Auditify;
use Symfony\Component\HttpFoundation\Response;

class Authorize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auditify::checkAuth($request)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access to Auditify.');
    }
}
