<?php

namespace App\Services\Exporter;

use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardService
{
    public function getOverview(): array
    {
        return Cache::remember('exporter_dashboard_overview', now()->addHours(12), function () {
            $totalGlobalDemand = DB::table('general_imports')->sum('value_million_usd');
            
            $verifiedImporters = Company::whereIn('type', ['importer', 'both'])
                                        ->count();

            return [
                'total_global_demand' => round($totalGlobalDemand, 2),
                'verified_importers'  => $verifiedImporters,
                'saved_reports'       => 0,
            ];
        });
    }

    public function getDemandTrends(int $year)
    {
        $cacheKey = "exporter_demand_trends_{$year}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($year) {
            return DB::table('general_imports')
                ->select('month', DB::raw('SUM(value_million_usd) as total_value'))
                ->where('year', $year)
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        });
    }

    public function getTopImporters(int $perPage, int $page): LengthAwarePaginator
    {
        $cacheKey = "exporter_top_importers_per_{$perPage}_page_{$page}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($perPage) {
            return Company::with('country:id,name_en,name_ar') 
                ->whereIn('type', ['importer', 'both'])
                ->orderBy('id', 'desc') 
                ->paginate($perPage);
        });
    }
}