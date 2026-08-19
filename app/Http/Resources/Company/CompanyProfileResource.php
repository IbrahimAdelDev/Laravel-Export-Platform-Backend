<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'type'        => $this->type,
            'email'       => $this->email,
            'website'     => $this->website,
            'extra_details' => $this->extra_details,
            'status'      => $this->status,

            // البلد
            'country'     => $this->whenLoaded('country', function () {
                return [
                    'id'     => $this->country->id,
                    'name_ar' => $this->country->name_ar,
                    'name_en' => $this->country->name_en,
                ];
            }),

            // العلاقات
            'locations'   => $this->whenLoaded('locations'),
            'phones'      => $this->whenLoaded('phones'),

            // المنتجات (البورتفوليو)
            'portfolio'   => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id'        => $product->id,
                        'hs_code_6' => $product->hs_code_6,
                        'name_en'   => $product->name_en,
                        'name_ar'   => $product->name_ar,
                    ];
                });
            }),
        ];
    }
}