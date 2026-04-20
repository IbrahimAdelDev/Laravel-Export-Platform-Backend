<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectTokenFromCookieMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // if token is present in the Authorization header.
        $tokenInHeader = $request->bearerToken();

        // if token is NOT present in the header, we will check the cookies for the access token.
        if (!$tokenInHeader) {

            // We will only check the cookies for the access token if the request is for the refresh route.
            $isRefreshRoute = $request->is('api/refresh');
            
            $cookieName = $isRefreshRoute ? 'refresh_token' : 'access_token';
            
            $tokenInCookie = $request->cookie($cookieName);
            
            // If we find the token in the cookie, we will set it in the Authorization header for the request.
            if ($tokenInCookie) {
                $request->headers->set('Authorization', 'Bearer ' . $tokenInCookie);
            }
            
        }

        /*
         Note: If $tokenInHeader was present from the beginning,
         the request will continue normally and the cookie will be ignored completely.
        */

        return $next($request);
    }
}
