<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LandingStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'verified_importers' => $this['importers_count'],
            'countries_analyzed' => $this['countries_count'],
            'hs_code_products'   => $this['products_count'],
        ];
    }
}