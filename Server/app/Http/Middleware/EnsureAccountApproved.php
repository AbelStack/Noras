<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not approved. Please wait for admin approval.',
            ], 403);
        }

        // Refresh user from database to get current account_status
        $user->refresh();

        if ($user->isSuspended()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account has been suspended',
                'account_status' => 'suspended',
            ], 403);
        }

        if (!$user->isApproved()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not approved. Please wait for admin approval.',
            ], 403);
        }

        return $next($request);
    }
}
