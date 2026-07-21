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
        'iso2_code',
        'iso3_code',
        'iso_numeric_code',
    ];

    // Companies located in this country
    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    // Trade statistics where this country is the origin (exports)
    public function generalExports()
    {
        return $this->hasMany(GeneralExport::class);
    }

    // Trade statistics where this country is the destination (imports)
    public function generalImports()
    {
        return $this->hasMany(GeneralImport::class);
    }

    // Trade statistics where this country is the origin (exports)
    public function outgoingTradeStatistics()
    {
        return $this->hasMany(TradeStatistic::class, 'origin_country_id');
    }

    // Trade statistics where this country is the destination (imports)
    public function incomingTradeStatistics()
    {
        return $this->hasMany(TradeStatistic::class, 'destination_country_id');
    }

}
