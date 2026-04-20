<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegisterService; 
use App\Traits\TokenResponseTrait;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use TokenResponseTrait;

    protected $registerService;

    public function __construct(RegisterService $registerService)
    {
        $this->registerService = $registerService;
    }

    public function register(RegisterRequest $request)
    {
        // Data validation is handled by the RegisterRequest, so we can directly call the service with the validated data.
        $result = $this->registerService->register($request->validated());

        return $this->respondWithTokens($result, 'Registration successful.', 201);
    }
}
