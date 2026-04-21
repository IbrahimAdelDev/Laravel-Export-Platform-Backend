<?php

namespace App\Services\Admin\DataImport;

use App\Contracts\ImportStrategyInterface;

class ImportStrategyManager
{
    public function getStrategy(string $importType): ImportStrategyInterface
    {
        $bindingName = ImportStrategyInterface::class . '_' . $importType;

        if (!app()->bound($bindingName)) {
            throw new \Exception("Import type [{$importType}] is not supported or not registered.");
        }

        return app()->make($bindingName);
    }
}