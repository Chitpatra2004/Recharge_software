<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspiciousRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('TRACE')) {
            abort(405);
        }

        if ($this->hasInvalidHost($request)) {
            abort(400);
        }

        $path = '/' . ltrim($request->decodedPath(), '/');
        $lowerPath = strtolower($path);

        if (str_starts_with($lowerPath, '/.well-known/acme-challenge/')) {
            return $next($request);
        }

        foreach (config('security.blocked_extensions', []) as $extension) {
            if (str_ends_with($lowerPath, strtolower($extension))) {
                abort(404);
            }
        }

        foreach (config('security.sensitive_path_patterns', []) as $pattern) {
            if (preg_match($pattern, $path) === 1) {
                abort(404);
            }
        }

        return $next($request);
    }

    private function hasInvalidHost(Request $request): bool
    {
        $allowedHosts = config('security.allowed_hosts', []);

        if ($allowedHosts === []) {
            return false;
        }

        $host = strtolower($request->getHost());

        foreach ($allowedHosts as $allowedHost) {
            $allowedHost = strtolower($allowedHost);

            if ($host === $allowedHost) {
                return false;
            }

            if (str_starts_with($allowedHost, '.') && str_ends_with($host, $allowedHost)) {
                return false;
            }
        }

        return true;
    }
}
