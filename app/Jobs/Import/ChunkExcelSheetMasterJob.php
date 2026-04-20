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

class ChunkExcelSheetMasterJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $importRecordId;
    public $type;
    public $sheetName;

    public function __construct($importRecordId, $type, $sheetName)
    {
        $this->importRecordId = $importRecordId;
        $this->type = $type;
        $this->sheetName = $sheetName;
    }

    public function handle()
    {
        $importRecord = ImportBatch::find($this->importRecordId);
        if (!$importRecord) {
            // Handle the case where the import record is not found (log error, throw exception, etc.)
            return;
        }
        $importRecord->update(['status' => 'processing']);
        $path = Storage::path($importRecord->file_path);

        $chunkSize = 300;
        $chunk = [];
        $totalRows = 0;
        $rowIndex = 0;

        $row2 = []; // This will hold the second row of the sheet which contains the years for mapping purposes
        $yearColumnMap = []; // This will map column indexes to years based on the second row of the sheet

        (new FastExcel)->sheet($this->sheetName)->withoutHeaders()->import($path, function ($row) use (&$chunk, &$rowIndex, &$row2, &$yearColumnMap, $chunkSize) {
            
            $rowIndex++;

            // Store the second row for year mapping
            if ($rowIndex === 2) {
                $row2 = $row;
                return; // Skip processing for the second row
            }

            // Map columns to years based on the second row (which contains the years in merged cells)
            if ($rowIndex === 3) {
                $currentYear = null;
                foreach ($row as $index => $val) {
                    // Check if the cell in the second row has a year value (not empty and numeric)
                    if (!empty($row2[$index]) && is_numeric($row2[$index])) {
                        $currentYear = $row2[$index];
                    }
                    
                    // If the column index is 4 or greater, and we have a current year, we can map this column to the year and type (qty or value)
                    if ($index >= 4 && $currentYear) {
                        $colType = (strpos(strtoupper($val), 'QTY') !== false) ? 'qty' : 'value';
                        $yearColumnMap[$index] = ['year' => $currentYear, 'type' => $colType];
                    }
                }
                return;
            }

            // Skip empty rows or rows that don't have a code in the first column
            if ($rowIndex < 4 || empty($row[0])) {
                return;
            }

            $chunk[] = $row;

            if (count($chunk) === $chunkSize) {
                // Dispatch a worker job to process this chunk of data, passing the yearColumnMap for correct processing
                $this->batch()->add(new ProcessProductsWorkerJob($this->type, $chunk, $yearColumnMap));
                $chunk = [];
            }
        });

        // dispatch the remaining chunk
        if (!empty($chunk)) {
            $this->batch()->add(new ProcessProductsWorkerJob($this->type, $chunk, $yearColumnMap));
            // $this->dispatchWorker($chunk);
        }

        // update the total row count for the progress bar
        $importRecord->update(['total_rows' => $totalRows]);
    }

    // private function dispatchWorker(array $chunk)
    // {
    //     if (in_array($this->batch()->type, ['agricultural_products', 'industrial_products'])) {
    //         ProcessProductsWorkerJob::dispatch($this->batch(), $chunk);
    //     }
    //     // Exporters worker goes here later
    // }
}