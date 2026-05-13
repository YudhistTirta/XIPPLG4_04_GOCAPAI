<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Api\SavingsTransactionController;
use App\Http\Controllers\Api\SavingsGoalController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// =====================
// AUTHENTICATION ROUTES
// =====================

// Fallback route for unauthenticated users missing the Accept: application/json header
Route::get('/login', function () {
    return response()->json([
        'success' => false,
        'message' => 'Unauthenticated. Please ensure you have added Authorization: Bearer {token} and Accept: application/json in your headers.'
    ], 401);
})->name('login');

Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });
});

// =====================
// PROTECTED ROUTES
// =====================
Route::middleware('auth:sanctum')->group(function () {
    // Savings Goal Routes
    Route::get('/savings-goals', [SavingsGoalController::class, 'index']);
    Route::post('/savings-goals', [SavingsGoalController::class, 'store']);
    Route::get('/savings-goals/{id}', [SavingsGoalController::class, 'show']);
    Route::put('/savings-goals/{id}', [SavingsGoalController::class, 'update']);
    Route::delete('/savings-goals/{id}', [SavingsGoalController::class, 'destroy']);
    Route::put('/savings-goals/{id}/status', [SavingsGoalController::class, 'updateStatus']);
    Route::get('/savings-goals/{id}/progress', [SavingsGoalController::class, 'getProgress']);

    // Savings Transaction Routes
    Route::post('/savings-goals/{goalId}/transactions', [SavingsTransactionController::class, 'store']);
});

// =====================
// CATEGORY ROUTES
// =====================
Route::get('/categories', [CategoryController::class, 'index']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
});
 