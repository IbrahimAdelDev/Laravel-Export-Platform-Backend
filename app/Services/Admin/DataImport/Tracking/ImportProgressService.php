<?php

namespace App\Services\Admin\DataImport\Tracking;

use App\Models\ImportBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;

class ImportProgressService
{
    // Retrieve progress details for a specific batch
    public function getProgressDetails(int $batchId): array
    {
        $importBatch = ImportBatch::findOrFail($batchId);

        $progressData = [
            'import_id'           => $importBatch->id,
            'file_name'           => $importBatch->file_name,
            'status'              => $importBatch->status,
            'processed_rows'      => $importBatch->processed_rows,
            'total_rows'          => $importBatch->total_rows,
            'progress_percentage' => 0,
            'queue_details'       => null,
        ];

        // If the queue is not linked yet, return the basic data
        if (!$importBatch->job_batch_id) {
            return $progressData;
        }

        $laravelBatch = Bus::findBatch($importBatch->job_batch_id);

        if ($laravelBatch) {
            $progressData['progress_percentage'] = $laravelBatch->progress();
            $progressData['queue_details'] = [
                'total_jobs'   => $laravelBatch->totalJobs,
                'pending_jobs' => $laravelBatch->pendingJobs,
                'failed_jobs'  => $laravelBatch->failedJobs,
            ];

            // Synchronize the database status with the actual queue status
            $this->syncBatchStatus($importBatch, $laravelBatch);
            
            // Update the status in the response array after synchronization
            $progressData['status'] = $importBatch->status; 
        }

        return $progressData;
    }

    // Synchronize the record status in the database based on the queue status (Fallback Sync)
    private function syncBatchStatus(ImportBatch $importBatch, Batch $laravelBatch): void
    {
        $newStatus = $importBatch->status;

        // Determine the correct status based on your Enum
        if ($laravelBatch->finished() && !$laravelBatch->hasFailures()) {
            $newStatus = 'completed';
        } elseif ($laravelBatch->hasFailures() || $laravelBatch->cancelled()) {
            $newStatus = 'failed';
        }

        // // Update the database only if the status has changed
        if ($newStatus !== $importBatch->status) {
            $importBatch->update([
                'status'       => $newStatus,
                'completed_at' => $newStatus === 'completed' ? now() : $importBatch->completed_at,
            ]);
        }
    }
}