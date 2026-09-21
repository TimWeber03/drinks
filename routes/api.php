<?php

use App\Http\Controllers\Api\V3\AuditController;
use App\Http\Controllers\Api\V3\BarcodeController;
use App\Http\Controllers\Api\V3\DenominationController;
use App\Http\Controllers\Api\V3\ImageController;
use App\Http\Controllers\Api\V3\ProductController;
use App\Http\Controllers\Api\V3\ServerController;
use App\Http\Controllers\Api\V3\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Space-Market API v3
|--------------------------------------------------------------------------
|
| https://space-market.github.io/API/swagger.json
|
*/

Route::get('info', [ServerController::class, 'info']);
Route::get('audits', [AuditController::class, 'index']);

Route::get('products', [ProductController::class, 'index']);
Route::post('products', [ProductController::class, 'store']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::patch('products/{product}', [ProductController::class, 'update']);
Route::delete('products/{product}', [ProductController::class, 'destroy']);

Route::get('users/stats', [UserController::class, 'stats']);
Route::get('users/barcode/{barcode}', [UserController::class, 'byBarcode']);
Route::get('users', [UserController::class, 'index']);
Route::post('users', [UserController::class, 'store']);
Route::get('users/{user}', [UserController::class, 'show']);
Route::patch('users/{user}', [UserController::class, 'update']);
Route::delete('users/{user}', [UserController::class, 'destroy']);
Route::post('users/{user}/deposit', [UserController::class, 'deposit']);
Route::post('users/{user}/spend', [UserController::class, 'spend']);
Route::post('users/{user}/buy/barcode', [UserController::class, 'buyByBarcode']);
Route::post('users/{user}/buy', [UserController::class, 'buy']);
Route::post('users/{user}/transfer', [UserController::class, 'transfer']);

Route::get('images', [ImageController::class, 'capabilities']);
Route::post('images', [ImageController::class, 'store']);
Route::get('images/{image}', [ImageController::class, 'show']);
Route::get('images/{image}/img', [ImageController::class, 'data']);

Route::post('barcodes', [BarcodeController::class, 'store']);
Route::get('barcodes/{barcode}', [BarcodeController::class, 'show']);
Route::patch('barcodes/{barcode}', [BarcodeController::class, 'update']);
Route::delete('barcodes/{barcode}', [BarcodeController::class, 'destroy']);

Route::get('denominations', [DenominationController::class, 'index']);
Route::post('denominations', [DenominationController::class, 'store']);
Route::get('denominations/{denomination}', [DenominationController::class, 'show']);
Route::patch('denominations/{denomination}', [DenominationController::class, 'update']);
Route::delete('denominations/{denomination}', [DenominationController::class, 'destroy']);
