<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            // يمكن إضافة الشركات المرتبطة بالمستخدم هنا إذا استدعت الحاجة باستخدام $this->whenLoaded
        ];
    }
}