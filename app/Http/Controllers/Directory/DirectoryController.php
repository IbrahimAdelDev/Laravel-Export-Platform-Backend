<?php

namespace App\Http\Controllers\Directory;

use App\Http\Controllers\Controller;
use App\Services\Directory\DirectoryService;
use App\Http\Requests\Directory\GetImportersRequest;
use App\Http\Requests\Directory\GetProductsRequest;
use App\Http\Resources\Directory\ImporterResource;
use App\Http\Resources\Directory\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class DirectoryController extends Controller
{
    public function __construct(
        protected DirectoryService $directoryService
    ) {}

    public function importers(GetImportersRequest $request): JsonResponse
    {
        $importers = $this->directoryService->getImporters($request->validated());

        return response()->json([
            'success' => true,
            'data'    => ImporterResource::collection($importers)->resolve(),
            'pagination' => [
                'current_page' => $importers->currentPage(),
                'total'        => $importers->total(),
                'last_page'    => $importers->lastPage(),
            ]
        ]);
    }

    public function products(GetProductsRequest $request): JsonResponse
    {
        $products = $this->directoryService->getProducts($request->validated());

        return response()->json([
            'success' => true,
            'data'    => ProductResource::collection($products)->resolve(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'total'        => $products->total(),
                'last_page'    => $products->lastPage(),
            ]
        ]);
    }

    public function productMarketDemand(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        
        $demandData = $this->directoryService->getProductMarketDemand($product->id);

        return response()->json([
            'success' => true,
            'data'    => array_merge(
                ['product_id' => $product->id, 'hs_code_6' => $product->hs_code_6],
                $demandData
            )
        ]);
    }
}