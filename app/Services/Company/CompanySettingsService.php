<?php

namespace App\Services\Company;

use App\Models\Company;
use Illuminate\Support\Facades\DB;

class CompanySettingsService
{
    public function getProfile(int $companyId)
    {
        return Company::with(['country', 'locations', 'phones', 'products'])
                      ->findOrFail($companyId);
    }

    public function updateProfile(int $companyId, array $data)
    {
        $company = Company::findOrFail($companyId);
        $company->update($data);
        return $company;
    }

    public function addLocation(int $companyId, array $data)
    {
        $company = Company::findOrFail($companyId);
        return $company->locations()->create($data); // بيضيف الـ company_id أوتوماتيك
    }

    public function deleteLocation(int $companyId, int $locationId): bool
    {
        // الفلترة بـ company_id حتمية لمنع مسح داتا شركات تانية
        return DB::table('locations')
                 ->where('company_id', $companyId)
                 ->where('id', $locationId)
                 ->delete();
    }

    public function addPhone(int $companyId, array $data)
    {
        $company = Company::findOrFail($companyId);
        return $company->phones()->create($data);
    }

    public function deletePhone(int $companyId, int $phoneId): bool
    {
        return DB::table('phones')
                ->where('phoneable_id', $companyId)
                ->where('phoneable_type', Company::class)
                ->where('id', $phoneId)
                ->delete() > 0;
    }

    public function addToPortfolio(int $companyId, int $productId)
    {
        $company = Company::findOrFail($companyId);
        // syncWithoutDetaching بتمنع تكرار نفس المنتج للشركة في الداتابيز
        $company->products()->syncWithoutDetaching([$productId]);
    }

    public function removeFromPortfolio(int $companyId, int $productId): int
    {
        $company = Company::findOrFail($companyId);
        return $company->products()->detach($productId);
    }
}