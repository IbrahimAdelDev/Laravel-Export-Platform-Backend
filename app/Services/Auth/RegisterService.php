<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Create the user
            $user = User::create([
                'name' => $data['user_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'user', // default role, can be changed later
            ]);

            // 2. Create the phone record and associate it with the user (polymorphic relationship)
            $user->phones()->createMany($data['phones']);

            return $this->authService->generateTokens($user);
        });
    }
}