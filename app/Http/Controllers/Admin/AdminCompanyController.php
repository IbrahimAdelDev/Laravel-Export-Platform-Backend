<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminCompanyService;
use App\Http\Requests\Admin\GetAdminCompaniesRequest;
use App\Http\Resources\Admin\AdminCompanyResource;
use Illuminate\Http\JsonResponse;

class AdminCompanyController extends Controller
{
    public function __construct(
        protected AdminCompanyService $companyService
    ) {}

    public function index(GetAdminCompaniesRequest $request): JsonResponse
    {
        $companies = $this->companyService->getCompanies($request->validated());

        return response()->json([
            'success' => true,
            'data'    => AdminCompanyResource::collection($companies)->resolve(),
            'pagination' => [
                'current_page' => $companies->currentPage(),
                'total'        => $companies->total(),
                'last_page'    => $companies->lastPage(),
            ]
        ]);
    }

    public function verify(int $id): JsonResponse
    {
        $company = $this->companyService->verifyCompany($id);

        return response()->json([
            'success' => true,
            'message' => 'تم توثيق الشركة بنجاح.',
            'data'    => new AdminCompanyResource($company)
        ]);
    }

    public function reject(int $id): JsonResponse
    {
        $company = $this->companyService->rejectCompany($id);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض توثيق الشركة.',
            'data'    => new AdminCompanyResource($company)
        ]);
    }
}