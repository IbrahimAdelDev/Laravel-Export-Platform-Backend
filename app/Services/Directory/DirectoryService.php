<?php

namespace App\Services\Directory;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class DirectoryService
{
    public function getImporters(array $filters): LengthAwarePaginator
    {
        $query = Company::with('country:id,name_en,name_ar')
            ->select('id', 'name', 'type', 'country_id')
            ->whereIn('type', ['importer', 'both']);

        if (!empty($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['hs_code'])) {
            $query->whereHas('importBatches.product', function ($q) use ($filters) {
                $q->where('hs_code_6', $filters['hs_code']);
            });
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    public function getProducts(array $filters): LengthAwarePaginator
    {
        $query = Product::select('id', 'hs_code_6', 'name_en', 'name_ar');

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('hs_code_6', 'like', $term)
                  ->orWhere('name_en', 'like', $term)
                  ->orWhere('name_ar', 'like', $term);
            });
        }

        $perPage = $filters['per_page'] ?? 20;
        return $query->paginate($perPage);
    }

    public function getProductMarketDemand(int $productId)
    {
        $demand = DB::table('general_imports')
            ->where('product_id', $productId)
            ->select(
                DB::raw('SUM(value_million_usd) as total_market_value'),
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(DISTINCT country_id) as demanding_countries_count')
            )
            ->first();

        return [
            'total_market_value'        => round($demand->total_market_value ?? 0, 2),
            'total_quantity'            => round($demand->total_quantity ?? 0, 2),
            'demanding_countries_count' => $demand->demanding_countries_count ?? 0,
        ];
    }
}