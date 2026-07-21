<?php

namespace App\Http\Resources\Exporter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopImporterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'country'  => $this->country ? $this->country->name_en : null,
            'website'  => $this->website,
            'verified' => (bool) $this->verified_status,
        ];
    }
}