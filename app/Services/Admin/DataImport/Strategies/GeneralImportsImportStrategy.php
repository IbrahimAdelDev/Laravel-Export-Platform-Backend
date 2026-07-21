<?php

namespace App\Services\Admin\DataImport\Strategies;

use App\Contracts\ImportStrategyInterface;
use App\Jobs\Import\GeneralImports\GeneralImportsMasterJob;
use Illuminate\Support\Facades\Bus;
use App\Models\ImportBatch;
use Illuminate\Bus\Batch;

class GeneralImportsImportStrategy implements ImportStrategyInterface
{
    public function startImport($batch, $sheetName, $mapping, $extraData = null)
    {
        $sheetIndex = (int) filter_var($sheetName, FILTER_SANITIZE_NUMBER_INT);
        if ($sheetIndex < 1) { $sheetIndex = 1; }

        $importRecordId = $batch->id;

        $busBatch = Bus::batch([
            new GeneralImportsMasterJob($importRecordId, $sheetIndex, $mapping)
        ])->name("Import: {$batch->type} - {$sheetName}")
        ->then(function (Batch $busBatch) use ($importRecordId) {
            ImportBatch::where('id', $importRecordId)->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
        })
        ->catch(function (Batch $busBatch, \Throwable $e) use ($importRecordId) {
            ImportBatch::where('id', $importRecordId)->update([
                'status' => 'failed',
                'errors' => json_encode(['critical_error' => $e->getMessage()]),
            ]);
        })
        ->dispatch();

        $batch->update(['job_batch_id' => $busBatch->id]);
    }
}