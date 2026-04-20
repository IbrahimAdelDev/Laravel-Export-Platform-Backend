<?php

namespace App\Services\Admin\DataImport\Strategies;

use App\Contracts\ImportStrategyInterface;
use App\Jobs\Import\ExportStatisticsMasterJob;
use Illuminate\Support\Facades\Bus;

class ExportStatisticsImportStrategy implements ImportStrategyInterface
{
    public function startImport($batch, $sheetName, $mapping, $extraData)
    {
        // استخراج الرقم من النص (مثال: "Sheet 2" -> 2)
        $sheetIndex = (int) filter_var($sheetName, FILTER_SANITIZE_NUMBER_INT);
        
        // التأكد إن الرقم صالح
        if ($sheetIndex < 1) { $sheetIndex = 1; }

        $busBatch = Bus::batch([
            new ExportStatisticsMasterJob($batch->id, $sheetIndex, $mapping, $extraData)
        ])->name("Import: {$batch->type} - {$sheetName}")->dispatch();

        $batch->update(['job_batch_id' => $busBatch->id]);
    }
}