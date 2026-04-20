<?php

namespace App\Jobs\Import;

use App\Models\Product;
use App\Models\Country;
use App\Models\ExportStatistic;
use App\Models\ImportBatch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessExportStatisticsWorkerJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; 
    public $backoff = [10, 30, 60]; 

    public $importRecordId; // نفس الاسم الجديد
    public $chunk;
    public $mapping;
    public $extraData;

    public function __construct($importRecordId, $chunk, $mapping, $extraData)
    {
        $this->importRecordId = $importRecordId;
        $this->chunk = $chunk;
        $this->mapping = $mapping;
        $this->extraData = $extraData;
    }

    public function handle()
    {
        if ($this->batch()->cancelled()) { return; }

        $originCountryId = $this->extraData['origin_country_id'];

        DB::transaction(function () use ($originCountryId) {
            
            foreach ($this->chunk as $row) {
                
                $hsCodeColumn      = $this->mapping['hs_code'] ?? null;
                $nameColumn        = $this->mapping['product_name'] ?? null;
                $destCountryColumn = $this->mapping['destination_country'] ?? null;
                $unitColumn        = $this->mapping['unit'] ?? null;

                if ($hsCodeColumn === null || empty($row[$hsCodeColumn])) {
                    continue; 
                }

                $hsCode = (string) $row[$hsCodeColumn];
                $productName = $row[$nameColumn] ?? 'غير محدد';
                $destCountryName = trim($row[$destCountryColumn] ?? '');

                if (empty($destCountryName)) {
                    continue; 
                }

                $product = Product::firstOrCreate(
                    ['hs_code' => $hsCode],
                    [
                        'name_ar' => $productName,
                        'category' => 'agricultural', 
                        'unit' => $this->mapUnit($row[$unitColumn] ?? '')
                    ]
                );

                $destinationCountry = Country::firstOrCreate(
                    ['name_ar' => $destCountryName]
                );

                if (isset($this->mapping['years'])) {
                    foreach ($this->mapping['years'] as $year => $cols) {
                        
                        $qtyColumn   = $cols['qty'] ?? null;
                        $valueColumn = $cols['value'] ?? null;

                        $qty = $qtyColumn !== null ? (float) ($row[$qtyColumn] ?? 0) : 0;
                        $val = $valueColumn !== null ? (float) ($row[$valueColumn] ?? 0) : 0;

                        if ($qty > 0 || $val > 0) {
                            ExportStatistic::updateOrCreate([
                                'origin_country_id'      => $originCountryId,
                                'destination_country_id' => $destinationCountry->id,
                                'product_id'             => $product->id,
                                'year'                   => $year,
                            ], [
                                'export_unit'           => $product->unit,
                                'total_export_quantity' => $qty,
                                'total_export_value'    => $val,
                            ]);
                        }
                    }
                }
            }
            
            // تحديث العدد باستخدام المتغير الصحيح اللي بيشاور على الـ ID في الداتابيز
            ImportBatch::where('id', $this->importRecordId)->increment('processed_rows', count($this->chunk));
        });
    }

    private function mapUnit($excelUnit) {
        $unitMap = [
            'صندوق كرتوني' => 'Carton Box', 
            'طن' => 'Ton', 
            'لتر' => 'liter', 
            'متر' => 'meter', 
            'قفص' => 'Crate', 
            'طن متري' => 'Metric Ton', 
            'عدد' => 'Piece', 
            'قطعة' => 'Piece', 
            'رطل' => 'Pound', 
            '1000عود' => '1000 Sticks',
            'برميل خشب' => 'Wooden Barrel',
            'UNCONTAI' => 'Uncontainerized',
            'CANES' => 'Canes',
            'hectometre' => 'Hectometer',
            'millilitre' => 'Milliliter',
        ];
        return $unitMap[trim($excelUnit)] ?? 'Ton'; 
    }
}