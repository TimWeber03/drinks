<?php

use App\Http\Controllers\Api\V1\AuditController;
use App\Http\Controllers\Api\V1\BarcodeController;
use App\Http\Controllers\Api\V1\DrinkController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Space-Market API v1
|--------------------------------------------------------------------------
|
| https://github.com/Space-Market/API/blob/v1/spec/swagger.yaml
|
| v1 has no base path: its endpoints live at the root and are suffixed with
| .json. Amounts are decimal euros, and the mutating user endpoints are GETs.
|
*/

Route::name('api.v1.')->where(['drink' => '[0-9]+', 'user' => '[0-9]+'])->group(function () {
    Route::get('audits.json', [AuditController::class, 'index'])->name('audits.index');

    Route::get('drinks.json', [DrinkController::class, 'index'])->name('drinks.index');
    Route::post('drinks.json', [DrinkController::class, 'store'])->name('drinks.store');
    Route::get('drinks/new.json', [DrinkController::class, 'create'])->name('drinks.create');
    Route::get('drinks/{drink}.json', [DrinkController::class, 'show'])->name('drinks.show');
    Route::patch('drinks/{drink}.json', [DrinkController::class, 'update'])->name('drinks.update');
    Route::delete('drinks/{drink}.json', [DrinkController::class, 'destroy'])->name('drinks.destroy');

    Route::get('barcodes.json', [BarcodeController::class, 'index'])->name('barcodes.index');
    Route::post('barcodes.json', [BarcodeController::class, 'store'])->name('barcodes.store');
    Route::get('barcodes/new.json', [BarcodeController::class, 'create'])->name('barcodes.create');
    Route::delete('barcodes/{barcode}.json', [BarcodeController::class, 'destroy'])->name('barcodes.destroy');

    Route::get('users.json', [UserController::class, 'index'])->name('users.index');
    Route::post('users.json', [UserController::class, 'store'])->name('users.store');
    Route::get('users/new.json', [UserController::class, 'create'])->name('users.create');
    Route::get('users/stats.json', [UserController::class, 'stats'])->name('users.stats');
    Route::get('users/{user}.json', [UserController::class, 'show'])->name('users.show');
    Route::patch('users/{user}.json', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}.json', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('users/{user}/deposit.json', [UserController::class, 'deposit'])->name('users.deposit');
    Route::get('users/{user}/payment.json', [UserController::class, 'payment'])->name('users.payment');
    Route::get('users/{user}/buy.json', [UserController::class, 'buy'])->name('users.buy');
    Route::post('users/{user}/buy_barcode.json', [UserController::class, 'buyByBarcode'])->name('users.buy-barcode');
});
