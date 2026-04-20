<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',
        'country_id',
        'city',
        'address',
        'lat',
        'lon',
        'area',
        'floor',
        'notes',
    ];

    // Each location belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Each location belongs to a country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
