<?php

namespace App\Services\Admin;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function getSystemStats(): array
    {
        return [
            'total_products' => Product::count(),
            // استخدام DB Raw مع الجداول الضخمة لسرعة العد
            'total_import_records' => DB::table('general_imports')->count(),
            
            // مراقبة الـ Queue
            'queue_status' => [
                'pending_jobs' => DB::table('jobs')->count(),
                'failed_jobs'  => DB::table('failed_jobs')->count(),
            ]
        ];
    }
}