<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_id',
        'type',
        'email',
        'website',
        'extra_details',
        'status',
        'verified_at',
    ];

    protected $casts = [
        'extra_details' => 'array',
        'verified_at' => 'datetime',
    ];

    // Country where the company is located
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // Products that the company produces or trades
    public function products()
    {
        return $this->belongsToMany(Product::class, 'company_products');
    }

    // Trade statistics involving this company
    public function tradeStatistics()
    {
        return $this->hasMany(TradeStatistic::class);
    }

    // Users associated with this company (e.g., employees, contacts)
    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('role', 'status') // include role and status from the pivot table
                    ->withTimestamps();
    }

    // Phones associated with the company (polymorphic relationship)
    public function phones()
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    // Locations associated with the company
    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
