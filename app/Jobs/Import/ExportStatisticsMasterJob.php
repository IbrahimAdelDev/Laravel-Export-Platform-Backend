<?php

namespace App\Jobs\Import;

use App\Models\ImportBatch;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportStatisticsMasterJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30];

    // استخدمنا اسم مختلف تماماً عن لارافيل
    public $importRecordId; 
    public $sheetIndex;
    public $mapping;
    public $extraData;

    public function __construct($importRecordId, $sheetIndex, $mapping, $extraData)
    {
        $this->importRecordId = $importRecordId;
        $this->sheetIndex = $sheetIndex;
        $this->mapping = $mapping;
        $this->extraData = $extraData;
    }

    public function handle()
    {
        if ($this->batch()->cancelled()) { return; }

        $importRecord = ImportBatch::find($this->importRecordId); 
        $absolutePath = Storage::disk('local')->path($importRecord->file_path);

        $chunkSize = 500;
        $chunk = [];
        $rowIndex = 0; // 1. ضفنا عداد الصفوف

        (new FastExcel)->sheet($this->sheetIndex)->withoutHeaders()->import($absolutePath, function ($row) use (&$chunk, &$rowIndex, $chunkSize) {
            
            $rowIndex++;

            // 2. هنتجاهل أول 3 صفوف بالكامل (عشان دي عناوين الجدول)
            if ($rowIndex <= 3) {
                return; 
            }

            $chunk[] = $row;
            
            if (count($chunk) === $chunkSize) {
                $this->batch()->add(new ProcessExportStatisticsWorkerJob($this->importRecordId, $chunk, $this->mapping, $this->extraData));
                $chunk = [];
            }
        });

        if (!empty($chunk)) {
            $this->batch()->add(new ProcessExportStatisticsWorkerJob($this->importRecordId, $chunk, $this->mapping, $this->extraData));
        }
    }
}