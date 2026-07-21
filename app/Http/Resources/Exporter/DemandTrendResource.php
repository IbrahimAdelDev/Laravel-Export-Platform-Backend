<?php

namespace App\Http\Resources\Exporter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DemandTrendResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $monthName = date('F', mktime(0, 0, 0, $this->month, 10));

        return [
            'month'       => $this->month,
            'month_name'  => $monthName,
            'total_value' => round($this->total_value, 2) . ' M USD',
        ];
    }
}