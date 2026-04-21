<?php

namespace App\Http\Controllers\Admin\DataImport;

use App\Http\Controllers\Controller;
use App\Services\Admin\DataImport\Tracking\ImportProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class TrackingProgressController extends Controller
{
    protected $progressService;

    // Inject the service via the constructor (Dependency Injection - SOLID principle)
    public function __construct(ImportProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    public function __invoke($id): JsonResponse
    {
        try {
            $progressData = $this->progressService->getProgressDetails($id);

            return $this->successResponse($progressData, 'Progress retrieved successfully.', 200);
            
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Import record not found.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while retrieving the import status.', 500);
        }
    }
}