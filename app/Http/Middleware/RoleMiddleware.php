<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Check if the user is authenticated and has one of the required roles
        if (!$user || !in_array($user->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have the required permissions to access this resource.',
            ], 403);
        }

        return $next($request);
    }
}
