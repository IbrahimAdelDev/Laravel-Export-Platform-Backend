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
use App\Http\Controllers\Directory\DirectoryController;
use App\Http\Controllers\Company\CompanySettingsController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\DataImport\ImportHistoryController;
use App\Http\Controllers\Admin\AdminCompanyController;
use App\Http\Controllers\Admin\AdminUserController;

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

    Route::prefix('directory')->group(function () {
        Route::get('/importers', [DirectoryController::class, 'importers']);
        Route::get('/products', [DirectoryController::class, 'products']);
        Route::get('/products/{id}/market-demand', [DirectoryController::class, 'productMarketDemand']);
    });

    Route::prefix('company')->group(function () {
        Route::get('/profile', [CompanySettingsController::class, 'getProfile']);
        Route::put('/profile', [CompanySettingsController::class, 'updateProfile']);
        
        Route::post('/locations', [CompanySettingsController::class, 'addLocation']);
        Route::delete('/locations/{id}', [CompanySettingsController::class, 'deleteLocation']);
        
        Route::post('/phones', [CompanySettingsController::class, 'addPhone']);
        Route::delete('/phones/{id}', [CompanySettingsController::class, 'deletePhone']);
        
        Route::post('/portfolio', [CompanySettingsController::class, 'addPortfolio']);
        Route::delete('/portfolio/{id}', [CompanySettingsController::class, 'deletePortfolio']);
    });
    
    Route::post('/companies', [CompanyController::class, 'store']);
    
    // Routes with role-based access control
    Route::middleware('role:super_admin, admin')->group(function () {
        Route::post('/imports/start', StartImportController::class);
        Route::post('/admin/preview-import', PreviewImportController::class);
        Route::get('/imports/{id}/progress', TrackingProgressController::class);
        Route::prefix('admin')->group(function () {
        
        // إحصائيات النظام
            Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);
            
            // إدارة رفع البيانات
            Route::get('/imports/history', ImportHistoryController::class);

            Route::get('/companies', [AdminCompanyController::class, 'index']);
            Route::post('/companies/{id}/verify', [AdminCompanyController::class, 'verify']);
            Route::post('/companies/{id}/reject', [AdminCompanyController::class, 'reject']);

            // إدارة المستخدمين
            Route::get('/users', [AdminUserController::class, 'index']);

        });
    });
});

// test
Route::post('/test', PreviewImportController::class);