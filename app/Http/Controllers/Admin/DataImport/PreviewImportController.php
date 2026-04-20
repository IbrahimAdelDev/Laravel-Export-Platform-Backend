<?php

namespace App\Http\Controllers\Admin\DataImport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DataImport\PreviewImportRequest;
use App\Services\Admin\DataImport\PreviewImportService;

class PreviewImportController extends Controller
{
    public function __invoke(PreviewImportRequest $request, PreviewImportService $previewService)
    {
        try {
            $data = $previewService->execute($request->file('file'));
            
            return response()->json([
                'success' => true,
                ...$data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}