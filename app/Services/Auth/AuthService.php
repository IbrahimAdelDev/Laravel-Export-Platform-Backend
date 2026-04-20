<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Handle user login and return an API token.
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        return $this->generateTokens($user);
    }

    public function refresh($user)
    {
        if (!$user->currentAccessToken()->can('issue-access-token')) {
            throw ValidationException::withMessages([
                'token' => ['Not authorized to token.'],
            ]);
        }

        $user->currentAccessToken()->delete();

        return $this->generateTokens($user);
    }

    public function generateTokens(User $user)
    {
        $accessToken = $user->createToken(
            'access_token',
            ['access-api'],
            now()->addMinutes(15)
        )->plainTextToken;

        // Create a refresh token with a longer expiration time and a specific ability
        $refreshToken = $user->createToken(
            'refresh_token',
            ['issue-access-token'],
            now()->addDays(14)
        )->plainTextToken;

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    public function logout($user)
    {
        $user->currentAccessToken()->delete();
        
        // if you want to revoke all tokens for the user (e.g., on logout from all devices), you can do:
        // $user->tokens()->delete();
    }
}