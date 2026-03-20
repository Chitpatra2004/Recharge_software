<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'api_user') {
            return response()->json(['message' => 'Access denied. Seller account required.'], 403);
        }

        if ($user->status === 'inactive') {
            return response()->json(['message' => 'Account pending admin approval.'], 403);
        }

        if ($user->status === 'suspended') {
            return response()->json(['message' => 'Account suspended. Contact support.'], 403);
        }

        return $next($request);
    }
}
