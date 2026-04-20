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
        // بنباصي اليوزر اللي عامل الريكويست (اللي جابه Sanctum) والداتا المفلترة
        $company = $this->companyService->createCompanyForUser($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الشركة بنجاح. جاري مراجعة البيانات من قبل الإدارة.',
            'data'    => $company
        ], 201);
    }
}
