<?php

namespace App\Jobs\Import\GeneralImports;

use App\Models\ImportBatch;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneralImportsMasterJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30];

    public $importRecordId; 
    public $sheetIndex;
    public $mapping;

    public function __construct($importRecordId, $sheetIndex, $mapping)
    {
        $this->importRecordId = $importRecordId;
        $this->sheetIndex = $sheetIndex;
        $this->mapping = $mapping;
    }

    public function handle()
    {
        if ($this->batch()->cancelled()) { return; }

        $importRecord = ImportBatch::find($this->importRecordId); 
        $importRecord->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        $absolutePath = Storage::disk('local')->path($importRecord->file_path);

        $chunkSize = 500;
        $chunk = [];
        $rowIndex = 0; 

        (new FastExcel)->sheet($this->sheetIndex)->withoutHeaders()->import($absolutePath, function ($row) use (&$chunk, &$rowIndex, $chunkSize) {
            
            $rowIndex++;

            // تخطي صف العناوين الأول فقط بناءً على شكل الشيت الجديد
            if ($rowIndex <= 1) {
                return; 
            }

            $chunk[] = $row;
            
            if (count($chunk) === $chunkSize) {
                $this->batch()->add(new ProcessGeneralImportsWorkerJob($this->importRecordId, $chunk, $this->mapping));
                $chunk = [];
            }
        });

        if (!empty($chunk)) {
            $this->batch()->add(new ProcessGeneralImportsWorkerJob($this->importRecordId, $chunk, $this->mapping));
        }

        $actualTotalRows = max(0, $rowIndex - 1);
        $importRecord->update(['total_rows' => $actualTotalRows]);
    }
}