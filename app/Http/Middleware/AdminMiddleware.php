<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if authenticated user is admin
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        return response()->json([
            'message' => 'Unauthorized access. Admins only.'
        ], 403);
    }
}