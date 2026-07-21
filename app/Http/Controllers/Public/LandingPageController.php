<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\LandingPageService;
use App\Http\Requests\Public\GetHighDemandProductsRequest;
use App\Http\Resources\Public\LandingStatsResource;
use App\Http\Resources\Public\HighDemandProductResource;
use Illuminate\Http\JsonResponse;

class LandingPageController extends Controller
{
    public function __construct(
        protected LandingPageService $landingService
    ) {}

    public function stats(): JsonResponse
    {
        $stats = $this->landingService->getStats();

        return response()->json([
            'success' => true,
            'data'    => new LandingStatsResource($stats)
        ]);
    }

    public function highDemandProducts(GetHighDemandProductsRequest $request): JsonResponse
    {
        $perPage = $request->validated('per_page', 3);
        $page = $request->query('page', 1);

        $products = $this->landingService->getHighDemandProducts($perPage, $page);

        return response()->json([
            'success' => true,
            'data'    => HighDemandProductResource::collection($products)->resolve(), 
            'pagination' => [
                'current_page' => $products->currentPage(),
                'total'        => $products->total(),
                'has_more'     => $products->hasMorePages(),
            ]
        ]);
    }
}