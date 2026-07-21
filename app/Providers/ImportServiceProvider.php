<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ImportStrategyInterface;
use App\Services\Admin\DataImport\Strategies\TradeStatisticsImportStrategy;
use App\Services\Admin\DataImport\Strategies\GeneralImportsImportStrategy;

class ImportServiceProvider extends ServiceProvider
{
    public function register()
    {
        // We register the strategies in the Container using the Type as a unique key
        $this->app->bind(ImportStrategyInterface::class . '_trade_statistics', function ($app) {
            return new TradeStatisticsImportStrategy();
        });

        $this->app->bind(
            ImportStrategyInterface::class . '_general_imports', 
            GeneralImportsImportStrategy::class
        );

        // Later:
        // $this->app->bind(ImportStrategyInterface::class . '_exporters', function ($app) { ... });
    }
}