<?php

namespace App\Services\Admin\DataImport\Strategies;

use App\Contracts\ImportStrategyInterface;
use App\Jobs\Import\ExportStatisticsMasterJob;
use Illuminate\Support\Facades\Bus;
use App\Models\ImportBatch;
use Illuminate\Bus\Batch;

class ExportStatisticsImportStrategy implements ImportStrategyInterface
{
    public function startImport($batch, $sheetName, $mapping, $extraData)
    {
        // Extract the number from the text (e.g., "Sheet 2" -> 2)
        $sheetIndex = (int) filter_var($sheetName, FILTER_SANITIZE_NUMBER_INT);

        $importRecordId = $batch->id;
        
        // Ensure the index is valid
        if ($sheetIndex < 1) { $sheetIndex = 1; }

        $busBatch = Bus::batch([
            new ExportStatisticsMasterJob($importRecordId, $sheetIndex, $mapping, $extraData)
        ])->name("Import: {$batch->type} - {$sheetName}")
        ->then(function (Batch $busBatch) use ($importRecordId) {
            // Executes automatically when all workers complete successfully (100%)
            ImportBatch::where('id', $importRecordId)->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
        })
        ->catch(function (Batch $busBatch, \Throwable $e) use ($importRecordId) {
            // Executes if there is a critical failure in the batch
            ImportBatch::where('id', $importRecordId)->update([
                'status' => 'failed',
                'errors' => json_encode(['critical_error' => $e->getMessage()]),
            ]);
        })
        ->dispatch();

        // Link the database record to the batch in the queue
        $batch->update(['job_batch_id' => $busBatch->id]);
    }
}