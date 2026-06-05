<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


//THESE ROUTES ONLY ADMIN CAN ACCESS
Route::middleware(['auth:api', 'role:admin'])->group(function (){
    //USER MANAGEMENT
    Route::post('create/user', [UserController::class, 'store']);
    Route::put('update/user/{id}', [UserController::class, 'update']);
    Route::delete('delete/user/{id}', [UserController::class, 'destroy']);
    Route::get('users/list', [UserController::class, 'index']);

    //CATEGORY MANAGEMENT
    Route::post('create/category', [CategoryController::class, 'store']);
    Route::put('update/category/{id}', [CategoryController::class, 'update']);
    Route::delete('delete/category/{id}', [CategoryController::class, 'destroy']);
    Route::get('categories/list', [CategoryController::class, 'index']);

    //PRODUCT MANAGEMENT 
    Route::post('create/product', [ProductController::class, 'store']);
    Route::put('update/product/{id}', [ProductController::class, 'update']);
    Route::delete('delete/product/{id}', [ProductController::class, 'destroy']);
    Route::get('products/list', [ProductController::class, 'index']);

    //CUSTOMER MANAGEMENT
    Route::post('create/customer', [CustomerController::class, 'store']);
    Route::put('update/customer/{id}', [CustomerController::class, 'update']);
    Route::delete('delete/customer/{id}', [CustomerController::class, 'destroy']);
    Route::get('customers/list', [CustomerController::class, 'index']);

});

//THE ROUTES CAN BE ACCESSED BY ALL USERS
Route::middleware('auth:api')->group(function (){
    Route::post('user/logout', [AuthController::class, 'logout']);
});
Route::post('user/login', [AuthController::class, 'login']);
Route::post('get/reset-password/token', [AuthController::class, 'store']);
Route::post('reset/password', [AuthController::class, 'resetPassword']);
