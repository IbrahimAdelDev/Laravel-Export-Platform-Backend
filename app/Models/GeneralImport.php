<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralImport extends Model
{
    use HasFactory;

    protected $table = 'general_imports';

    protected $fillable = [
        'country_id',
        'product_id',
        'year',
        'month',
        'unit',
        'quantity',
        'value_million_usd',
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