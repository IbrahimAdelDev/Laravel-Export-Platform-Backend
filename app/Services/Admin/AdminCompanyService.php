<?php

namespace App\Services\Admin;

use App\Models\Company;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminCompanyService
{
    public function getCompanies(array $filters): LengthAwarePaginator
    {
        $query = Company::with('country:id,name_en,name_ar')->latest();

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    public function verifyCompany(int $companyId): Company
    {
        $company = Company::findOrFail($companyId);
        
        $company->update([
            'status'      => 'verified', // افتراض أن لديك حالة verified في ה-Enum
            'verified_at' => now(),
        ]);

        return $company->load('country');
    }

    public function rejectCompany(int $companyId): Company
    {
        $company = Company::findOrFail($companyId);
        
        $company->update([
            'status'      => 'rejected', // افتراض أن لديك حالة rejected في ال-Enum
            'verified_at' => null,
        ]);

        return $company->load('country');
    }
}