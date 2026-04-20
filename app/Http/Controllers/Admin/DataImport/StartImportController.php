<?php

namespace App\Http\Controllers\Admin\DataImport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DataImport\StartImportRequest;
use App\Services\Admin\DataImport\ImportManagerService;

class StartImportController extends Controller
{
    public function __invoke(StartImportRequest $request, ImportManagerService $importManager)
    {
        try {
            $user = $request->user() ?? \App\Models\User::first();

            if (!$user) {
                return response()->json(['error' => 'لا يوجد مستخدمين في قاعدة البيانات لإتمام العملية'], 401);
            }

            // نمرر البيانات للـ Manager Service
            $batches = $importManager->handleMultipleSheets($user, $request->validated());
            // $batches = $importManager->handleMultipleSheets($request->user(), $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'تم بدء معالجة الشيتات المختارة في الخلفية.',
                'data'    => ['batches' => $batches]
            ], 202);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}