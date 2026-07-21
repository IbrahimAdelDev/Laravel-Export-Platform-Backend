<?php

namespace App\Http\Controllers\Exporter;

use App\Http\Controllers\Controller;
use App\Services\Exporter\DashboardService;
use App\Http\Requests\Exporter\GetDemandTrendsRequest;
use App\Http\Requests\Exporter\GetTopImportersRequest;
use App\Http\Resources\Exporter\DemandTrendResource;
use App\Http\Resources\Exporter\TopImporterResource;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function overview(): JsonResponse
    {
        $data = $this->dashboardService->getOverview();

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }

    public function demandTrends(GetDemandTrendsRequest $request): JsonResponse
    {
        $year = $request->validated('year', date('Y')); // السنة الحالية كافتراضي
        $trends = $this->dashboardService->getDemandTrends($year);

        return response()->json([
            'success' => true,
            'data'    => DemandTrendResource::collection($trends)
        ]);
    }

    public function topImporters(GetTopImportersRequest $request): JsonResponse
    {
        $perPage = $request->validated('per_page', 5);
        $page = $request->validated('page', 1);

        $importers = $this->dashboardService->getTopImporters($perPage, $page);

        return response()->json([
            'success' => true,
            'data'    => TopImporterResource::collection($importers)->resolve(),
            'pagination' => [
                'current_page' => $importers->currentPage(),
                'total'        => $importers->total(),
                'has_more'     => $importers->hasMorePages(),
            ]
        ]);
    }
}