<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportBatch extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'errors' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor to calculate progress percentage for frontend display
    public function getProgressPercentageAttribute()
    {
        if ($this->total_rows === 0) return 0;
        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }
}
