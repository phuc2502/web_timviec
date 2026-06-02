<?php

use App\Http\Controllers\ListingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Job Search Filter
|--------------------------------------------------------------------------
|
| GET /api/listings/search  — tìm kiếm, lọc, phân trang tin tuyển dụng
| GET /api/listings/cities  — danh sách địa chỉ distinct
| GET /api/skills            — danh sách tất cả kỹ năng
|
*/

Route::get('/listings/search', [ListingController::class, 'index']);
Route::get('/listings/cities', [ListingController::class, 'cities']);
Route::get('/skills',          [ListingController::class, 'skills']);
