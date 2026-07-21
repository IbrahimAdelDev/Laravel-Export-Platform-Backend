<?php

namespace App\Services\Public;

use App\Models\Company;
use App\Models\Country;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class LandingPageService
{
    public function getStats(): array
    {
        return Cache::remember('public_landing_stats', now()->addDay(), function () {
            return [
                'importers_count' => Company::whereIn('type', ['importer', 'both'])->count(),
                'countries_count' => Country::count(),
                'products_count'  => Product::count(),
            ];
        });
    }

    public function getHighDemandProducts(int $perPage, int $page): LengthAwarePaginator
    {
        $cacheKey = "public_high_demand_products_per_{$perPage}_page_{$page}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($perPage) {
            return Product::query()
                ->select(
                    'products.id', 
                    'products.name_en', 
                    'products.name_ar', 
                    'products.hs_code_6', 
                    'products.category'
                )
                ->join('general_imports', 'products.id', '=', 'general_imports.product_id')
                ->selectRaw('SUM(general_imports.value_million_usd) as total_demand_value')
                ->groupBy(
                    'products.id', 
                    'products.name_en', 
                    'products.name_ar', 
                    'products.hs_code_6', 
                    'products.category'
                )
                ->orderByDesc('total_demand_value')
                ->paginate($perPage);
        });
    }
}