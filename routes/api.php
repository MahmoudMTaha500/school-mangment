<?php

use App\Modules\IdentityAccess\Interfaces\Http\Controllers\PlatformAuthController;
use App\Modules\Tenancy\Interfaces\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::middleware('throttle:api')->group(function (): void {
    Route::post('/platform/login', [PlatformAuthController::class, 'login']);
    Route::middleware(['auth:sanctum', 'platform-admin'])->group(function (): void {
        Route::post('/schools', [SchoolController::class, 'store']);
    });
});
