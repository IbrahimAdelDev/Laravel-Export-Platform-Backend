<?php

namespace App\Services\Admin\DataImport;

use App\Models\ImportBatch;
use App\Models\ActivityLog;
use App\Jobs\Import\ChunkExcelSheetMasterJob;
use Illuminate\Support\Facades\Bus;

class UploadExcelService
{
    public function execute($user, $file, array $types)
    {
        // 1. Generate a unique file name to prevent overwriting existing files
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $fileName = $originalName . '_' . now()->format('Y_m_d_His') . '.' . $extension;

        $path = $file->store('imports', 'local'); // Storing the file in 'storage/app/imports'
        $batches = [];

        // Mapping of types to their corresponding sheet names for better logging and job dispatching
        $sheetMapping = [
            'agricultural_products'  => 'سلع حاصلات زراعية',
            'industrial_products'    => 'سلع صناعات غذائية',
            'agricultural_exporters' => 'مصدرين حاصلات زراعية',
            'industrial_exporters'   => 'مصدرين صناعات غذائية',
        ];

        foreach ($types as $type) {
            // 2. Creating a batch for each sheet
            $importRecord = ImportBatch::create([
                'user_id'   => $user->id,
                'file_name' => $fileName,
                'file_path' => $path,
                'type'      => $type,
                'status'    => 'pending'
                // total_rows, processed_rows, and errors will be updated by the job as it processes the file
            ]);

            $batchRecords[] = $importRecord;

            // 3. Logging the action in the Activity Log
            ActivityLog::create([
                'causer_id'   => $user->id,
                'causer_type' => get_class($user),
                'subject_id'  => $importRecord->id,
                'subject_type'=> get_class($importRecord),
                'description' => "Starting data import for sheet: " . $sheetMapping[$type],
                'properties'  => ['batch_id' => $importRecord->id, 'sheet_name' => $sheetMapping[$type]]
                // log name and other fields can be added as needed
                // log_name, ip_address, user_agent
            ]);

            // 4. Dispatching a job to process the sheet in chunks
            $busBatch = Bus::batch([
                // Passing the import record ID, type, and sheet name to the job for processing
                new ChunkExcelSheetMasterJob($importRecord->id, $type, $sheetMapping[$type])
            ])->name("Import: {$type}")->dispatch();

            // Updating the import record with the batch ID for tracking
            $importRecord->update(['job_batch_id' => $busBatch->id]);
            // Note: The actual job dispatching is handled by the Bus facade, and the ChunkExcelSheetMasterJob will be responsible for processing the file in chunks and updating the import record accordingly.
            // ChunkExcelSheetMasterJob::dispatch($importRecord, $sheetMapping[$type]);
        }

        return $batchRecords;
    }
}