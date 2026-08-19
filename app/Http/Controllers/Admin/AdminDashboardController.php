<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected AdminDashboardService $dashboardService
    ) {}

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->dashboardService->getSystemStats()
        ]);
    }
}