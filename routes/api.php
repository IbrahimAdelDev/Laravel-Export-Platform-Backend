<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Admin\DataImport\PreviewImportController;
use App\Http\Controllers\Admin\DataImport\StartImportController;
use App\Http\Controllers\Admin\DataImport\TrackingProgressController;
use App\Http\Controllers\Public\LandingPageController;
use App\Http\Controllers\Exporter\DashboardController;
use App\Http\Controllers\Analysis\MarketAnalysisController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('public')->group(function () {
    Route::get('/landing-stats', [LandingPageController::class, 'stats']);
    Route::get('/high-demand-products', [LandingPageController::class, 'highDemandProducts']);
});

Route::prefix('auth')->group(function () {
    // Public Auth Routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [RegisterController::class, 'register']);

    // Protected Auth Routes (Refresh)
    Route::post('/refresh', [AuthController::class, 'refresh'])
        ->middleware(['auth:sanctum', 'abilities:issue-access-token']);
        
    Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'abilities:access-api']);
});


// Routes for protected endpoints, protected by Sanctum and checking for the 'access-api' ability.
Route::middleware(['auth:sanctum', 'abilities:access-api'])->group(function () {

    Route::prefix('dashboard/exporter')->group(function () {
        Route::get('/overview', [DashboardController::class, 'overview']);
        Route::get('/demand-trends', [DashboardController::class, 'demandTrends']);
        Route::get('/top-importers', [DashboardController::class, 'topImporters']);
    });

    Route::prefix('analysis')->group(function () {
        Route::get('/recommendations', [MarketAnalysisController::class, 'recommendations']);
        Route::get('/market-analytics/{hs_code}', [MarketAnalysisController::class, 'marketAnalytics']);
    });
    
    Route::post('/companies', [CompanyController::class, 'store']);
    
    // Routes with role-based access control
    Route::middleware('role:super_admin, admin')->group(function () {
        Route::post('/imports/start', StartImportController::class);
        Route::post('/admin/preview-import', PreviewImportController::class);
        Route::get('/imports/{id}/progress', TrackingProgressController::class);
    });
});

// test
Route::post('/test', PreviewImportController::class);