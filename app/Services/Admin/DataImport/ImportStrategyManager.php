<?php

namespace App\Services\Admin\DataImport;

use App\Contracts\ImportStrategyInterface;

class ImportStrategyManager
{
    public function getStrategy(string $importType): ImportStrategyInterface
    {
        $bindingName = ImportStrategyInterface::class . '_' . $importType;

        if (!app()->bound($bindingName)) {
            throw new \Exception("نوع الاستيراد [{$importType}] غير مدعوم أو غير مسجل.");
        }

        return app()->make($bindingName);
    }
}