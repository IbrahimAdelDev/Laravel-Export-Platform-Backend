<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminUserService;
use App\Http\Requests\Admin\GetAdminUsersRequest;
use App\Http\Resources\Admin\AdminUserResource;
use Illuminate\Http\JsonResponse;

class AdminUserController extends Controller
{
    public function __construct(
        protected AdminUserService $userService
    ) {}

    public function index(GetAdminUsersRequest $request): JsonResponse
    {
        // نمرر الـ ID الخاص بالمدير الحالي للسيرفس لاستثنائه من النتائج
        $users = $this->userService->getUsers($request->validated(), $request->user()->id);

        return response()->json([
            'success' => true,
            'data'    => AdminUserResource::collection($users)->resolve(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'total'        => $users->total(),
                'last_page'    => $users->lastPage(),
            ]
        ]);
    }
}