<?php

namespace App\Http\Resources\Directory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'hs_code_6' => $this->hs_code_6,
            'name'      => [
                'en' => $this->name_en,
                'ar' => $this->name_ar,
            ]
        ];
    }
}