<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ImportStrategyInterface;
use App\Services\Admin\DataImport\Strategies\ExportStatisticsImportStrategy;

class ImportServiceProvider extends ServiceProvider
{
    public function register()
    {
        // We register the strategies in the Container using the Type as a unique key
        $this->app->bind(ImportStrategyInterface::class . '_export_statistics', function ($app) {
            return new ExportStatisticsImportStrategy();
        });

        // Later:
        // $this->app->bind(ImportStrategyInterface::class . '_exporters', function ($app) { ... });
    }
}