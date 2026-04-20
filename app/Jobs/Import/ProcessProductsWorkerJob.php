<?php

namespace App\Jobs\Import;

// use App\Models\ImportBatch;
use App\Models\Product;
use App\Models\Country;
use App\Models\ExportStatistic;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessProductsWorkerJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Retry Logic
    public $backoff = [10, 30, 60]; // Backoff times in seconds for retries

    public $type;
    public $chunk;
    public $yearColumnMap;

    public function __construct($type, array $chunk, array $yearColumnMap)
    {
        $this->type = $type;
        $this->chunk = $chunk;
        $this->yearColumnMap = $yearColumnMap;
    }

    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return; // Stop processing if the batch has been cancelled
        }

        $category = $this->type === 'agricultural_products' ? 'agricultural' : 'industrial';

        // Let's assume the country is always Egypt for this import, as the data is about Egyptian products and exporters
        $egypt = Country::firstOrCreate(['name_ar' => 'مصر'], ['name_en' => 'Egypt', 'iso_code' => 'EG']);
        
        DB::transaction(function () use ($category, $egypt) {
            foreach ($this->chunk as $row) {
                
                // 1. main product data
                $hsCode = (string) $row[0];
                $productName = $row[1];
                $countryName = trim($row[2]);
                
                // transform unit from Arabic to English using a mapping (you can expand this mapping as needed)
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
                $unit = $unitMap[trim($row[3])] ?? 'Ton';

                // 2. insert or get the product based on hs_code (unique identifier)
                $product = Product::firstOrCreate(
                    ['hs_code' => $hsCode],
                    ['name_ar' => $productName, 'category' => $category, 'unit' => $unit]
                );

                // 3. insert or get the destination country
                $destinationCountry = Country::firstOrCreate(
                    ['name_ar' => $countryName]
                );

                // 4. build yearly statistics dynamically
                $statsData = [];
                
                // تجميع الكمية والقيمة لكل سنة
                foreach ($this->yearColumnMap as $index => $meta) {
                    $year = $meta['year'];
                    $type = $meta['type']; // 'qty' أو 'value'
                    $cellValue = (float) ($row[$index] ?? 0);

                    if (!isset($statsData[$year])) {
                        $statsData[$year] = ['qty' => 0, 'value' => 0];
                    }

                    if ($type === 'qty') {
                        $statsData[$year]['qty'] = $cellValue;
                    } else {
                        // Million dollars
                        $statsData[$year]['value'] = $cellValue; 
                    }
                }

                // 5. insert or update export statistics for this product and destination country
                foreach ($statsData as $year => $data) {
                    if ($data['qty'] > 0 || $data['value'] > 0) { // Therefore, whoever stores zeros and reduces the database size.
                        ExportStatistic::updateOrCreate([
                            'origin_country_id'      => $egypt->id,
                            'destination_country_id' => $destinationCountry->id,
                            'product_id'             => $product->id,
                            'year'                   => $year,
                        ], [
                            'export_unit'           => $unit,
                            'total_export_quantity' => $data['qty'],
                            'total_export_value'    => $data['value'],
                        ]);
                    }
                }
            }
        });
    }

    // public function handle()
    // {
    //     $insertData = [];

    //     foreach ($this->chunk as $row) {
    //         $insertData[] = [
    //             'hs_code'     => $row[0], // عمود 10-Digits Code
    //             'name_ar'     => $row[1], // عمود 10-Digits Desc
    //             'category'    => $this->batch->type === 'agricultural_products' ? 'agricultural' : 'industrial',
    //             'created_at'  => now(),
    //             'updated_at'  => now(),
    //         ];
    //     }

    //     // use insertOrIgnore to skip duplicates based on hs_code (unique index)
    //     DB::table('products')->insertOrIgnore($insertData);

    //     // update the processed rows count for the progress bar
    //     $this->batch->increment('processed_rows', count($this->chunk));

    //     // check if the batch is completed after processing this chunk
    //     $freshBatch = $this->batch->fresh();
    //     if ($freshBatch->processed_rows >= $freshBatch->total_rows) {
    //         $freshBatch->update([
    //             'status' => 'completed',
    //             'completed_at' => now()
    //         ]);
    //     }
    // }
}