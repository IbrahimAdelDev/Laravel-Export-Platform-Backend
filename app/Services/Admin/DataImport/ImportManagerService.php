<?php

namespace App\Services\Admin\DataImport;

use App\Models\ImportBatch;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class ImportManagerService
{
    protected $strategyManager;

    public function __construct(ImportStrategyManager $strategyManager)
    {
        $this->strategyManager = $strategyManager;
    }

    public function handleMultipleSheets($user, array $data)
    {
        $importType = $data['import_type'];
        // Retrieve the strategy from the manager (Inversion of Control principle)
        $strategy = $this->strategyManager->getStrategy($importType);

        $batches = [];

        foreach ($data['sheets_mapping'] as $sheetMapping) {
            // Use a transaction per sheet so that if one sheet fails, the others can still process normally
            DB::transaction(function () use ($user, $data, $strategy, $importType, $sheetMapping, &$batches) {
                
                $batch = ImportBatch::create([
                    'user_id'   => $user->id,
                    'file_name' => basename($data['file_path']),
                    'file_path' => $data['file_path'], // Relative path
                    'type'      => $importType,
                    'status'    => 'pending'
                ]);

                ActivityLog::create([
                    'log_name'     => 'Data_import',
                    'description'  => "Starting import of type ({$importType}) for sheet: {$sheetMapping['sheet_name']}",

                    'causer_id'    => $user->id,
                    'causer_type'  => get_class($user),

                    'subject_id'   => $batch->id,
                    'subject_type' => get_class($batch),

                    'properties'   => [
                        'sheet_name'        => $sheetMapping['sheet_name'],
                        'origin_country_id' => $data['origin_country_id']
                    ],

                    'ip_address'   => request()->ip(),
                    'user_agent'   => request()->userAgent(),
                ]);

                // Here we pass "Sheet 1" to the Strategy
                $strategy->startImport(
                    $batch, 
                    $sheetMapping['sheet_name'], 
                    $sheetMapping['columns'], 
                    ['origin_country_id' => $data['origin_country_id']]
                );

                $batches[] = $batch;
            });
        }

        return $batches;
    }
}