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
        // جلب الاستراتيجية من المانيجر (مبدأ Inversion of Control)
        $strategy = $this->strategyManager->getStrategy($importType);

        $batches = [];

        foreach ($data['sheets_mapping'] as $sheetMapping) {
            // الترانزاكشن لكل شيت عشان لو شيت فشل، الشيتات التانية تشتغل عادي
            DB::transaction(function () use ($user, $data, $strategy, $importType, $sheetMapping, &$batches) {
                
                $batch = ImportBatch::create([
                    'user_id'   => $user->id,
                    'file_name' => basename($data['file_path']),
                    'file_path' => $data['file_path'], // Relative path
                    'type'      => $importType,
                    'status'    => 'pending'
                ]);

                ActivityLog::create([
                    'causer_id'   => $user->id,
                    'causer_type' => get_class($user),
                    'description' => "بدء استيراد نوع ({$importType}) للشيت: {$sheetMapping['sheet_name']}",
                ]);

                // هنا بنباصي "Sheet 1" للـ Strategy
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