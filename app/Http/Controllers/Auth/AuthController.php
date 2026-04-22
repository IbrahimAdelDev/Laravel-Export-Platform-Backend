<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use App\Traits\TokenResponseTrait;

class AuthController extends Controller
{
    use TokenResponseTrait;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        return $this->respondWithTokens($result, 'Login successful.', 200);
    }

    public function refresh(Request $request)
    {
        $result = $this->authService->refresh($request->user());

        return $this->respondWithTokens($result, 'Tokens refreshed successfully.', 200);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        // To log out the user, we need to delete the access token and refresh token cookies from the client side.
        $cookieAccess = cookie()->forget('access_token');
        $cookieRefresh = cookie()->forget('refresh_token');

        return $this->successResponse([], 'Logged out successfully.')
                    ->withCookie($cookieAccess)
                    ->withCookie($cookieRefresh);
    }
}
