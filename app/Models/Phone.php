<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Phone extends Model
{
    use HasFactory;

    protected $fillable = ['phone', 'label', 'phoneable_id', 'phoneable_type'];

    // Define the polymorphic relationship to the owning model (Company or User)
    public function phoneable()
    {
        return $this->morphTo();
    }
}
