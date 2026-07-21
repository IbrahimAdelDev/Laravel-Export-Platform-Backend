<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'hs_code_6',
        'hs_code_8',
        'hs_code_10',
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

    // Trade statistics involving this product
    public function tradeStatistics()
    {
        return $this->hasMany(TradeStatistic::class);
    }

    // Total exports of this product globally
    public function generalExports()
    {
        return $this->hasMany(GeneralExport::class);
    }

    // Total imports of this product globally
    public function generalImports()
    {
        return $this->hasMany(GeneralImport::class);
    }

    // Users interested in this product
    public function interestedUsers()
    {
        return $this->belongsToMany(User::class, 'user_interests');
    }
}
