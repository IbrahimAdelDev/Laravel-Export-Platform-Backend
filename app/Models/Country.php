<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'iso_code',
    ];

    // Companies located in this country
    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    // Trade statistics where this country is the origin (exports)
    public function exports()
    {
        return $this->hasMany(TradeStatistic::class, 'origin_country_id');
    }

    // Trade statistics where this country is the destination (imports)
    public function imports()
    {
        return $this->hasMany(TradeStatistic::class, 'destination_country_id');
    }

    // Global market demands for this country
    public function exportStatistics()
    {
        return $this->hasMany(ExportStatistic::class);
    }
}
