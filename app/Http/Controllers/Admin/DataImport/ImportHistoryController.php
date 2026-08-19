<?php

namespace App\Http\Controllers\Admin\DataImport;

use App\Http\Controllers\Controller;
use App\Services\Admin\ImportHistoryService;
use App\Http\Resources\Admin\ImportBatchResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportHistoryController extends Controller
{
    public function __construct(
        protected ImportHistoryService $historyService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $history = $this->historyService->getHistory((int) $perPage);

        return response()->json([
            'success' => true,
            'data'    => ImportBatchResource::collection($history)->resolve(),
            'pagination' => [
                'current_page' => $history->currentPage(),
                'total'        => $history->total(),
                'last_page'    => $history->lastPage(),
            ]
        ]);
    }
}