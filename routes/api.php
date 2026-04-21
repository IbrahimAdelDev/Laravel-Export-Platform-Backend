<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Admin\DataImport\PreviewImportController;
use App\Http\Controllers\Admin\DataImport\StartImportController;
use App\Http\Controllers\Admin\DataImport\TrackingProgressController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// مسارات عامة
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// 1. Route for refreshing tokens, protected by Sanctum and checking for the 'issue-access-token' ability on the refresh token.
Route::post('/refresh', [AuthController::class, 'refresh'])
    ->middleware(['auth:sanctum', 'abilities:issue-access-token']);

// 2. Routes for protected endpoints, protected by Sanctum and checking for the 'access-api' ability.
Route::middleware('auth:sanctum', 'abilities:access-api')->group(function () {
    
    Route::post('/companies', [CompanyController::class, 'store']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Routes with role-based access control
    Route::middleware('role:super_admin, admin')->group(function () {
        Route::post('/admin/preview-import', PreviewImportController::class);
    });
});
// test
Route::post('/test', PreviewImportController::class);
Route::post('/test2', StartImportController::class);
Route::get('/imports/{id}/progress', TrackingProgressController::class);