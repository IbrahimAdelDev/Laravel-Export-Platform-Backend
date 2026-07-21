<?php

namespace App\Http\Resources\Analysis;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'country' => [
                'en' => $this['name_en'],
                'ar' => $this['name_ar'],
            ],
            'match_score'       => $this['match_score'] . '%', 
            'market_volume'     => round($this['total_volume'], 2) . ' M USD',
            'average_price'     => round($this['avg_price'], 4) . ' M USD per unit',
            'potential_revenue' => isset($this['potential_revenue']) 
                                    ? round($this['potential_revenue'], 2) . ' M USD' 
                                    : null,
            'recommendation_reason' => 'Huge order volume with a highly competitive price.'
        ];
    }
}