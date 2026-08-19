<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'type'        => $this->type,
            'status'      => $this->status,
            'country'     => $this->whenLoaded('country', function () {
                // بنتشيك لو الدولة موجودة أصلاً عشان ميرميش إيرور
                return $this->country ? [
                    'en' => $this->country->name_en,
                    'ar' => $this->country->name_ar,
                ] : null;
            }),
            'verified_at' => $this->verified_at ? $this->verified_at->format('Y-m-d H:i:s') : null,
            'created_at'  => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}