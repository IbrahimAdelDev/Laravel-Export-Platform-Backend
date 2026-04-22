<?php

namespace App\Http\Controllers\Admin\DataImport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DataImport\StartImportRequest;
use App\Services\Admin\DataImport\ImportManagerService;
use Exception;

class StartImportController extends Controller
{
    public function __invoke(StartImportRequest $request, ImportManagerService $importManager)
    {
        try {
            $batches = $importManager->handleMultipleSheets($request->user(), $request->validated());

            return $this->successResponse(
                ['batches' => $batches], 
                'The selected sheets have started processing in the background.', 
                202
            );
            
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}