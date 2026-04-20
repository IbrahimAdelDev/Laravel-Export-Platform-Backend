<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ImportStrategyInterface;
use App\Services\Admin\DataImport\Strategies\ExportStatisticsImportStrategy;

class ImportServiceProvider extends ServiceProvider
{
    public function register()
    {
        // بنسجل الاستراتيجيات في الـ Container باستخدام الـ Type كاسم مميز
        $this->app->bind(ImportStrategyInterface::class . '_export_statistics', function ($app) {
            return new ExportStatisticsImportStrategy();
        });

        // لاحقاً:
        // $this->app->bind(ImportStrategyInterface::class . '_exporters', function ($app) { ... });
    }
}