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
            $batches = $importManager->handleMultipleSheets($request->user(), $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'The selected sheets have started processing in the background.',
                'data'    => ['batches' => $batches]
            ], 202);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}