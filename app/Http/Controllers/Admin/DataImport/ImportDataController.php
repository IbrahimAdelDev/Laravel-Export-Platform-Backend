<?php

namespace App\Http\Controllers\Admin\DataImport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\DataImport\ImportExcelRequest;
use App\Services\Admin\DataImport\UploadExcelService;

class ImportDataController extends Controller
{
    // Injecting the UploadExcelService to handle the file processing logic
    public function __invoke(ImportExcelRequest $request, UploadExcelService $uploadService)
    {
        // Data Hadled by ImportExelRequest.
        $batches = $uploadService->execute(
            $request->user(), 
            $request->file('file'), 
            $request->validated('types') // Send only the selected types to the service for processing
        );

        return response()->json([
            'success' => true,
            'message' => 'File received successfully, processing selected sheets in the background.',
            'data' => [
                'batches' => $batches
            ]
        ], 202);
    }
}
