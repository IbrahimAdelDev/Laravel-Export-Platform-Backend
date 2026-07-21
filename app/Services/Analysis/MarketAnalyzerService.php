<?php

namespace App\Services\Analysis;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MarketAnalyzerService
{
    private function getProductMarketData(string $hsCode)
    {
        $product = Product::where('hs_code_6', $hsCode)->firstOrFail();
        $cacheKey = "market_data_hs_{$hsCode}";

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($product) {
            return DB::table('general_imports')
                ->select(
                    'countries.name_en', // جلب الإنجليزي
                    'countries.name_ar', // جلب العربي
                    DB::raw('SUM(general_imports.value_million_usd) as total_volume'),
                    DB::raw('SUM(general_imports.quantity) as total_quantity')
                )
                ->join('countries', 'general_imports.country_id', '=', 'countries.id')
                ->where('general_imports.product_id', $product->id)
                ->groupBy('countries.id', 'countries.name_en', 'countries.name_ar') // Group by للعمودين
                ->havingRaw('total_quantity > 0') 
                ->get()
                ->map(function ($item) {
                    $item->avg_price = $item->total_volume / $item->total_quantity;
                    return $item;
                });
        });
    }

    public function getRecommendations(string $hsCode, ?float $quantity = null): array
    {
        $marketData = $this->getProductMarketData($hsCode);

        if ($marketData->isEmpty()) {
            return [];
        }

        $maxVolume = $marketData->max('total_volume');
        
        $recommendations = $marketData->map(function ($item) use ($maxVolume, $quantity) {
            $volumeScore = ($item->total_volume / $maxVolume) * 100;
            
            $data = [
                'name_en'      => $item->name_en, // تمرير الإنجليزي
                'name_ar'      => $item->name_ar, // تمرير العربي
                'total_volume' => $item->total_volume,
                'avg_price'    => $item->avg_price,
                'match_score'  => round($volumeScore, 1) 
            ];

            if ($quantity) {
                $data['potential_revenue'] = $quantity * $item->avg_price;
            }

            return $data;
        });

        return $recommendations->sortByDesc('match_score')->take(3)->values()->toArray();
    }


    public function getDeepAnalytics(string $hsCode)
    {
        return $this->getProductMarketData($hsCode)
                    ->sortByDesc('total_volume')
                    ->take(5) 
                    ->values();
    }
}