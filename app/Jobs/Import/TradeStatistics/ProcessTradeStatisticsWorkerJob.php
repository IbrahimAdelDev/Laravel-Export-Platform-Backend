<?php

namespace App\Jobs\Import\TradeStatistics;

use App\Models\Product;
use App\Models\Country;
use App\Models\TradeStatistic;
use App\Models\ImportBatch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessTradeStatisticsWorkerJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; 
    public $backoff = [10, 30, 60]; 

    public $importRecordId;
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
            
            $dataToInsert = [];
            $now = Carbon::now();

            foreach ($this->chunk as $row) {
                
                $hsCodeColumn      = $this->mapping['hs_code'] ?? null;
                $nameColumn        = $this->mapping['product_name'] ?? null;
                $destCountryColumn = $this->mapping['destination_country'] ?? null;
                $unitColumn        = $this->mapping['unit'] ?? null;

                if ($hsCodeColumn === null || empty($row[$hsCodeColumn])) {
                    continue; 
                }

                $rawHsCode = preg_replace('/[^0-9]/', '', (string) $row[$hsCodeColumn]);
                
                // If the cleaned HS code is less than 6 digits, we skip it since it's not valid for our purposes
                if (strlen($rawHsCode) < 6) {
                    continue; 
                }

                // Getting the different levels of HS codes
                $hsCode6  = substr($rawHsCode, 0, 6);
                $hsCode8  = strlen($rawHsCode) >= 8 ? substr($rawHsCode, 0, 8) : null;
                $hsCode10 = strlen($rawHsCode) >= 10 ? substr($rawHsCode, 0, 10) : null;

                $productName = $row[$nameColumn] ?? 'Not specified';
                $destCountryName = trim($row[$destCountryColumn] ?? '');

                if (empty($destCountryName)) {
                    continue; 
                }

                $product = Product::firstOrCreate(
                    [
                        'hs_code_6'  => $hsCode6,
                        'hs_code_8'  => $hsCode8,
                        'hs_code_10' => $hsCode10,
                    ],
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
                            $dataToInsert[] = [
                                'origin_country_id'      => $originCountryId,
                                'destination_country_id' => $destinationCountry->id,
                                'product_id'             => $product->id,
                                'company_id'             => null, 
                                'year'                   => $year,
                                'month'                  => 0, // الإحصائية السنوية من الإكسيل الثابت
                                'unit'                   => $product->unit,
                                'quantity'               => $qty,
                                'value_million_usd'      => $val,
                                'created_at'             => $now,
                                'updated_at'             => $now,
                            ];
                        }
                    }
                }
            }

            if (!empty($dataToInsert)) {
                TradeStatistic::upsert(
                    $dataToInsert,
                    // Columns that define uniqueness for upsert
                    ['origin_country_id', 'destination_country_id', 'product_id', 'year', 'month'], 
                    // Columns to update if a duplicate is found
                    ['quantity', 'value_million_usd', 'unit', 'updated_at']
                );
            }
            
            // Update the count using the correct variable that points to the database ID
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