<?php

use App\Http\Controllers\Api\Admin\BannedKeywordController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ModerationController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\PublicListingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SkillController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider or via the Application's
| route registration callback.
|
*/

// ==========================================
// 1. PUBLIC ENDPOINTS (No Auth Required)
// ==========================================
Route::get('/listings', [PublicListingController::class, 'index']);
Route::get('/listings/{id}', [PublicListingController::class, 'show']);
Route::post('/listings/{id}/apply-click', [PublicListingController::class, 'applyClick']);

Route::get('/skills/search', [SkillController::class, 'search']);

// ==========================================
// 2. AUTHENTICATED ENDPOINTS (Requires Auth)
// ==========================================
Route::middleware('auth')->group(function () {

    // Candidate actions
    Route::post('/listings/{id}/report', [ReportController::class, 'store']);

    // Skills creation
    Route::post('/skills', [SkillController::class, 'store']);

    // Employer actions
    Route::prefix('employer')->group(function () {
        Route::get('/listings', [ListingController::class, 'index']);
        Route::get('/listings/{id}', [ListingController::class, 'show']);
        
        // Quota and rate limiting are applied to listing creation & cloning
        Route::post('/listings', [ListingController::class, 'store'])
            ->middleware(['quota', 'ratelimit.listing']);
            
        Route::put('/listings/{id}', [ListingController::class, 'update']);
        Route::delete('/listings/{id}', [ListingController::class, 'destroy']);
        
        // Lifecycle actions
        Route::post('/listings/{id}/pause', [ListingController::class, 'pause']);
        Route::post('/listings/{id}/resume', [ListingController::class, 'resume']);
        Route::post('/listings/{id}/close', [ListingController::class, 'close']);
        
        Route::post('/listings/{id}/renew', [ListingController::class, 'renew'])
            ->middleware('quota');
            
        Route::post('/listings/{id}/clone', [ListingController::class, 'clone'])
            ->middleware(['quota', 'ratelimit.listing']);
    });

    // Listing Analytics (Owner or Admin check is handled in controller)
    Route::get('/listings/{id}/analytics', [AnalyticsController::class, 'show']);

    // ==========================================
    // 3. ADMIN ENDPOINTS (Requires Admin)
    // ==========================================
    Route::prefix('admin')->group(function () {
        // Moderation
        Route::get('/listings/pending', [ModerationController::class, 'pending']);
        Route::post('/listings/{id}/approve', [ModerationController::class, 'approve']);
        Route::post('/listings/{id}/reject', [ModerationController::class, 'reject']);
        Route::get('/listings/{id}/audit-logs', [ModerationController::class, 'auditLogs']);
        Route::delete('/listings/{id}/hard-delete', [ModerationController::class, 'hardDelete']);

        // Violation Reports
        Route::get('/listings/reports', [ReportController::class, 'index']);
        Route::post('/listings/reports/{id}/review', [ReportController::class, 'review']);

        // Category CRUD
        Route::apiResource('categories', CategoryController::class);

        // Banned Keyword CRUD
        Route::apiResource('banned-keywords', BannedKeywordController::class);

        // System Analytics
        Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
    });
});
