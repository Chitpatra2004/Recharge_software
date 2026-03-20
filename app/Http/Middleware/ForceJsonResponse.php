<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ForceJsonResponse — prevents HTML error pages leaking on API routes.
 *
 * Without this, an unhandled exception on an API route returns Laravel's
 * HTML Whoops page (debug mode) or a plain HTML 500 page (production).
 * Either leaks implementation details and breaks clients expecting JSON.
 *
 * This middleware adds the Accept: application/json header before the
 * request reaches the application, causing Laravel's exception handler
 * to render JSON responses for all errors automatically.
 *
 * Apply to all API route groups in bootstrap/app.php:
 *   $middleware->appendToGroup('api', ForceJsonResponse::class);
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        // Force JSON rendering for all API errors (401, 403, 404, 500, etc.)
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
