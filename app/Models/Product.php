<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'hs_code',
        'name_ar',
        'name_en',
        'category',
        'unit',
        'indicative_price',
        'price_unit',
    ];

    // Companies that produce or trade this product
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_products');
    }

    // Trade statistics for this product
    public function tradeStatistics()
    {
        return $this->hasMany(TradeStatistic::class);
    }

    // Global market demands for this product
    public function exportStatistics()
    {
        return $this->hasMany(ExportStatistic::class);
    }

    // Users interested in this product
    public function interestedUsers()
    {
        return $this->belongsToMany(User::class, 'user_interests');
    }
}
