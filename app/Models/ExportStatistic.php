<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExportStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'origin_country_id',
        'destination_country_id',
        'product_id',
        'year',
        'export_unit',
        'total_export_quantity',
        'total_export_value',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
