<?php

namespace App\Http\Resources\Analysis;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketAnalyticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'country' => [
                'en' => $this->name_en,
                'ar' => $this->name_ar,
            ],
            'volume' => round($this->total_volume, 2), 
            'price'  => round($this->avg_price, 4),    
        ];
    }
}