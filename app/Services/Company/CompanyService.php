<?php

namespace App\Services\Company;

use App\Models\Company;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    public function createCompanyForUser($user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            
            // 1. Create the company with status 'pending'
            $company = Company::create([
                'name'          => $data['name'],
                'country_id'    => $data['country_id'],
                'type'          => $data['type'],
                'email'         => $data['email'] ?? null,
                'website'       => $data['website'] ?? null,
                'extra_details' => $data['extra_details'] ?? null,
                'status'        => 'pending', 
            ]);

            // 2. Create the company's location (headquarters)
            $company->locations()->create([
                'type'    => 'headquarters',
                'country_id' => $data['location']['country_id'],
                'address' => $data['location']['address'],
                'city'    => $data['location']['city'],
            ]);

            // 3. Create the company's phones (polymorphic relationship)
            $company->phones()->createMany($data['phones']);

            // 4. Linking the user to the company as Owner in the Pivot Table
            $user->companies()->attach($company->id, [
                'role'   => 'owner',
                'status' => 'pending',
            ]);

            return $company;
        });
    }
}