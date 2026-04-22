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
            
            return $this->successResponse($data, 'Preview generated successfully.');
            
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}