<?php

namespace App\Jobs\Import\GeneralImports;

use App\Models\Product;
use App\Models\Country;
use App\Models\GeneralImport;
use App\Models\ImportBatch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessGeneralImportsWorkerJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public $importRecordId;
    public $chunk;
    public $mapping;

    public function __construct($importRecordId, $chunk, $mapping)
    {
        $this->importRecordId = $importRecordId;
        $this->chunk = $chunk;
        $this->mapping = $mapping;
    }

    public function handle()
    {
        if ($this->batch()->cancelled()) { return; }

        DB::transaction(function () {
            $dataToInsert = [];
            $now = Carbon::now();

            foreach ($this->chunk as $row) {
                
                // 1. الربط الذكي للمنتج (HS Code)
                $hsCodeColumn = $this->mapping['hs_code'] ?? null;
                $hsCode = trim($row[$hsCodeColumn] ?? '');
                if (empty($hsCode)) continue;

                $productNameColumn = $this->mapping['product_name'] ?? null;
                
                $product = Product::firstOrCreate(
                    ['hs_code_6' => $hsCode],
                    [
                        'name_en'  => trim($row[$productNameColumn] ?? 'Unknown'),
                        'category' => 'agricultural',
                        'unit'     => 'Ton'
                    ]
                );

                // 2. الربط الذكي للدولة (ISO Code) لمنع التكرار
                $countryIsoColumn = $this->mapping['country_iso'] ?? null;
                $isoCode = trim($row[$countryIsoColumn] ?? '');
                if (empty($isoCode)) continue;

                $countryNameColumn = $this->mapping['country_name'] ?? null;

                $country = Country::firstOrCreate(
                    ['iso3_code' => $isoCode],
                    [
                        'name_en' => trim($row[$countryNameColumn] ?? 'Unknown'),
                    ]
                );

                // 3. التجهيز الرياضي (تحويل من الكيلو للطن، ومن الدولار للمليون)
                $qtyColumn = $this->mapping['quantity'] ?? null;
                $valColumn = $this->mapping['value'] ?? null;
                $yearColumn = $this->mapping['year'] ?? null;

                $rawQty = (float) ($row[$qtyColumn] ?? 0);
                $rawVal = (float) ($row[$valColumn] ?? 0);

                $qtyInTon = $rawQty / 1000;
                $valInMillion = $rawVal / 1000000;

                // إهمال السجلات الصفرية لتخفيف الداتابيز
                if ($qtyInTon <= 0 && $valInMillion <= 0) continue;

                $dataToInsert[] = [
                    'country_id'              => $country->id,
                    'product_id'              => $product->id,
                    'year'                    => (int) ($row[$yearColumn] ?? 0),
                    'month'                   => 0, // سنوي
                    'unit'                    => 'Ton',
                    'quantity'          => $qtyInTon,
                    'value_million_usd' => $valInMillion,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ];
            }

            // 4. تنفيذ أمر Upsert واحد للـ Chunk بالكامل
            if (!empty($dataToInsert)) {
                GeneralImport::upsert(
                    $dataToInsert,
                    ['country_id', 'product_id', 'year', 'month'], // Unique Keys
                    ['quantity', 'value_million_usd', 'updated_at'] // Update Columns
                );
            }

            // تحديث التقدم
            ImportBatch::where('id', $this->importRecordId)->increment('processed_rows', count($this->chunk));
        });
    }
}