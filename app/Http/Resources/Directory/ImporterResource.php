<?php

namespace App\Http\Resources\Directory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImporterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'type'        => $this->type,
            'country'     => [
                'en' => $this->whenLoaded('country', fn() => $this->country->name_en),
                'ar' => $this->whenLoaded('country', fn() => $this->country->name_ar),
            ],
            'is_verified' => $this->is_verified ?? false,
        ];
    }
}