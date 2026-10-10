<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        // Refresh user from database to get current account_status
        $user->refresh();

        if (!$user->isApproved()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Admin account suspended or not approved.',
            ], 403);
        }

        return $next($request);
    }
}
