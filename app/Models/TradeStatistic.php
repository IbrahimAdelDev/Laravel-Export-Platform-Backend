<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TradeStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'origin_country_id',
        'destination_country_id',
        'product_id',
        'company_id',
        'year',
        'unit',
        'quantity',
        'value_m_usd',
    ];

    // Origin country of the trade
    public function originCountry()
    {
        return $this->belongsTo(Country::class, 'origin_country_id');
    }

    // Destination country of the trade
    public function destinationCountry()
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    // Product involved in the trade
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Company involved in the trade
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
