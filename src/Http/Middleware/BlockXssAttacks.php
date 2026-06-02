<?php

namespace Auditify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auditify\Facades\Auditify;

class BlockXssAttacks
{
    /**
     * Common XSS attack signatures.
     *
     * @var array
     */
    protected $patterns = [
        '/<script[\s\S]*?>/i',
        '/javascript\s*:/i',
        '/\bon[a-zA-Z]+\s*=\s*/i',
        '/<iframe[\s\S]*?>/i',
        '/<object[\s\S]*?>/i',
        '/<embed[\s\S]*?>/i',
        '/<svg[\s\S]*?>/i',
        '/@import\s+/i',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Check if XSS protection is enabled
        if (!config('auditify.xss_protection.enabled', true)) {
            return $next($request);
        }

        // 2. Check if route is excluded from scanning
        if ($this->shouldExclude($request)) {
            return $next($request);
        }

        // 3. Scan all incoming request inputs and route parameters recursively
        $inputs = array_merge(
            $request->all(),
            $request->route() ? $request->route()->parameters() : []
        );

        $this->scanInputs($inputs, $request);

        return $next($request);
    }

    /**
     * Recursively scan inputs for XSS payloads.
     */
    protected function scanInputs(array $inputs, Request $request, string $prefix = ''): void
    {
        foreach ($inputs as $key => $value) {
            $fullKey = $prefix ? $prefix . '.' . $key : $key;

            if (is_array($value)) {
                $this->scanInputs($value, $request, $fullKey);
            } elseif (is_string($value)) {
                $this->checkValue($fullKey, $value, $request);
            }
        }
    }

    /**
     * Check a single string value for XSS signatures.
     */
    protected function checkValue(string $key, string $value, Request $request): void
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                // Log the security warning with critical severity
                Auditify::logSecurity(
                    'XSS Attack Attempt Detected',
                    "Suspicious XSS payload detected in parameter [{$key}]: \"{$value}\"",
                    'critical'
                );

                // Block the request if enabled
                if (config('auditify.xss_protection.block', true)) {
                    abort(403, 'Request blocked due to suspicious activity.');
                }

                // If block is disabled, we break to avoid duplicate logging of the same parameter
                break;
            }
        }
    }

    /**
     * Determine if the request URI is excluded from scanning.
     */
    protected function shouldExclude(Request $request): bool
    {
        $exclusions = config('auditify.xss_protection.exclude_routes', []);

        foreach ($exclusions as $exclusion) {
            if ($request->is($exclusion)) {
                return true;
            }
        }

        return false;
    }
}
