<?php

namespace App\Http\Controllers\Analysis;

use App\Http\Controllers\Controller;
use App\Services\Analysis\MarketAnalyzerService;
use App\Http\Requests\Analysis\GetRecommendationsRequest;
use App\Http\Resources\Analysis\RecommendationResource;
use App\Http\Resources\Analysis\MarketAnalyticsResource;
use Illuminate\Http\JsonResponse;

class MarketAnalysisController extends Controller
{
    public function __construct(
        protected MarketAnalyzerService $analyzerService
    ) {}

    public function recommendations(GetRecommendationsRequest $request): JsonResponse
    {
        $hsCode = $request->validated('hs_code');
        $quantity = $request->validated('quantity');

        $recommendations = $this->analyzerService->getRecommendations($hsCode, $quantity);

        return response()->json([
            'success' => true,
            'data'    => RecommendationResource::collection($recommendations)->resolve()
        ]);
    }

    public function marketAnalytics(string $hsCode): JsonResponse
    {
        if (!preg_match('/^\d{6}$/', $hsCode)) {
            return response()->json(['success' => false, 'message' => 'Invalid HS Code format. Must be 6 digits.'], 400);
        }

        $analytics = $this->analyzerService->getDeepAnalytics($hsCode);

        return response()->json([
            'success' => true,
            'data'    => MarketAnalyticsResource::collection($analytics)->resolve()
        ]);
    }
}