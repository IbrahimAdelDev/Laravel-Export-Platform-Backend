<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Services\Company\CompanyService;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function store(StoreCompanyRequest $request)
    {
        // Benbasy, the user who made the request (the one Sanctum brought) and the filtered data
        $company = $this->companyService->createCompanyForUser($request->user(), $request->validated());

        return $this->successResponse(
            $company, 
            'The company is active and registered. The data is currently being reviewed by management.', 
            201
        );
    }
}
