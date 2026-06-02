<?php

namespace Auditify\Http\Middleware;

use Auditify\Facades\Auditify;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisits
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
        $response = $next($request);

        if (! config('auditify.track_page_visits', true)) {
            return $response;
        }

        // Only log GET requests for standard page visits (ignore AJAX/PJAX)
        if ($request->method() === 'GET' && ! $request->ajax() && ! $request->pjax()) {
            $path = trim($request->path(), '/');
            $prefix = trim(config('auditify.route_prefix', 'auditify'), '/');

            // Skip logging Auditify dashboard/logs page visits to prevent loops
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $response;
            }

            // Log page visit as activity log
            Auditify::logActivity(
                'Page Visit: /' . $path,
                $request->fullUrl()
            );
        }

        return $response;
    }
}
