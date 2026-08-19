<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Services\Company\CompanySettingsService;
use App\Http\Requests\Company\UpdateProfileRequest;
use App\Http\Requests\Company\StoreLocationRequest;
use App\Http\Requests\Company\StorePhoneRequest;
use App\Http\Requests\Company\StorePortfolioRequest;
use App\Http\Resources\Company\CompanyProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    public function __construct(
        protected CompanySettingsService $settingsService
    ) {}

    /**
     * Helper Method عشان نجيب الـ ID بتاع شركة المستخدم الحالي
     */
    private function getCompanyId(Request $request): int
    {
        // بنجيب أول شركة مربوطة باليوزر من خلال الجدول الوسيط (العلاقة اللي ضفناها في موديل User)
        $company = $request->user()->companies()->first();

        // لو اليوزر مش مربوط بأي شركة، بنرمي إيرور عشان نحمي السيرفس من إنها تاخد null
        if (!$company) {
            abort(403, 'المستخدم الحالي غير مرتبط بأي شركة.');
        }

        return $company->id; 
    }

    public function getProfile(Request $request): JsonResponse
    {
        $profile = $this->settingsService->getProfile($this->getCompanyId($request));
        
        return response()->json([
            'success' => true,
            'data'    => new CompanyProfileResource($profile)
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $this->settingsService->updateProfile($this->getCompanyId($request), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات الشركة بنجاح'
        ]);
    }

    public function addLocation(StoreLocationRequest $request): JsonResponse
    {
        $location = $this->settingsService->addLocation($this->getCompanyId($request), $request->validated());

        return response()->json(['success' => true, 'data' => $location], 201);
    }

    public function deleteLocation(int $id, Request $request): JsonResponse
    {
        $deleted = $this->settingsService->deleteLocation($this->getCompanyId($request), $id);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'الفرع غير موجود أو لا تملك صلاحية حذفه'], 403);
        }

        return response()->json(['success' => true, 'message' => 'تم حذف الفرع بنجاح']);
    }

    public function addPhone(StorePhoneRequest $request): JsonResponse
    {
        $phone = $this->settingsService->addPhone($this->getCompanyId($request), $request->validated());

        return response()->json(['success' => true, 'data' => $phone], 201);
    }

    public function deletePhone(int $id, Request $request): JsonResponse
    {
        $deleted = $this->settingsService->deletePhone($this->getCompanyId($request), $id);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'الرقم غير موجود أو لا تملك صلاحية حذفه'], 403);
        }

        return response()->json(['success' => true, 'message' => 'تم حذف الرقم بنجاح']);
    }

    public function addPortfolio(StorePortfolioRequest $request): JsonResponse
    {
        $this->settingsService->addToPortfolio($this->getCompanyId($request), $request->product_id);

        return response()->json(['success' => true, 'message' => 'تم إضافة المنتج لملف الشركة'], 201);
    }

    public function deletePortfolio(int $id, Request $request): JsonResponse
    {
        $this->settingsService->removeFromPortfolio($this->getCompanyId($request), $id);

        return response()->json(['success' => true, 'message' => 'تم إزالة المنتج من ملف الشركة']);
    }
}