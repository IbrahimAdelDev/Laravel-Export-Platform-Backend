<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HighDemandProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name_en'      => $this->name_en,
            'name_ar'      => $this->name_ar,
            'hs_code'      => $this->hs_code_6,
            'category'     => $this->category,
            'total_demand' => round($this->total_demand_value, 2) . ' M USD', 
        ];
    }
}