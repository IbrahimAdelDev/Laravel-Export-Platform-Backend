<?php

namespace App\Traits;

trait TokenResponseTrait
{
    protected function respondWithTokens(array $data, string $message, int $statusCode = 200)
    {
        $cookieAccess = cookie('access_token', $data['access_token'], 15, '/', null, false, true);
        $cookieRefresh = cookie('refresh_token', $data['refresh_token'], 14 * 24 * 60, '/', null, false, true);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                // Cloning the user object to avoid modifying the original instance that might be used elsewhere in the application.
                'user' => clone $data['user'], 
                'tokens' => [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                    'token_type' => 'Bearer'
                ]
            ]
        ], $statusCode)->withCookie($cookieAccess)->withCookie($cookieRefresh);
    }
}