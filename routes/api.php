<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;

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

/*user*/
Route::post('/user/register', [UserController::class, 'register']);
Route::post('/user/login', [UserController::class, 'login']);
/*category*/
Route::get('/category', [CategoryController::class, 'index']);
Route::post('/category', [CategoryController::class, 'store']);
/*product*/
Route::get('/product', [ProductController::class, 'index']);
Route::post('/product', [ProductController::class, 'store']);
Route::get('/product/{uuid}', [ProductController::class, 'show']);
Route::put('/product/{uuid}', [ProductController::class, 'update']);
Route::delete('/product/{uuid}', [ProductController::class, 'destroy']);
/*product*/
Route::post('/transaction', [TransactionController::class, 'store']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
