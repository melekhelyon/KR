<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\BudgetsController;
use App\Http\Controllers\AdminController;


Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);

    Route::get('/account', [AccountsController::class, 'get']);
    Route::post('/account/create', [AccountsController::class, 'store']);
    Route::put('/account/update/{id}', [AccountsController::class, 'update']);
    Route::delete('/account/delete/{id}', [AccountsController::class, 'delete']);

    Route::get('/category', [CategoriesController::class, 'get']);
    Route::post('/category/create', [CategoriesController::class, 'store']);
    Route::put('/category/update/{id}', [CategoriesController::class, 'update']);
    Route::delete('/category/delete/{id}', [CategoriesController::class, 'delete']);

    Route::get('/goals', [GoalsController::class, 'get']);
    Route::post('/goals/create', [GoalsController::class, 'store']);
    Route::put('/goals/update/{id}', [GoalsController::class, 'update']);
    Route::delete('/goals/delete/{id}', [GoalsController::class, 'delete']);
    Route::post('/goals/{id}/progress', [GoalsController::class, 'updateProgress']);

    Route::get('/budgets', [BudgetsController::class, 'get']);
    Route::post('/budgets/create', [BudgetsController::class, 'store']);
    Route::put('/budgets/update/{id}', [BudgetsController::class, 'update']);
    Route::delete('/budgets/delete/{id}', [BudgetsController::class, 'delete']);

    Route::get('/operations', [OperationsController::class, 'get']);
    Route::post('/operations/create', [OperationsController::class, 'store']);
    Route::post('/operations/transfer', [OperationsController::class, 'transfer']);
    
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::put('/users/{id}/role', [AdminController::class, 'updateUserRole']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::get('/roles', [AdminController::class, 'roles']);
    });
});