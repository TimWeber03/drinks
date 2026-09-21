<?php

use App\Livewire\Admin\DrinkerManager;
use App\Livewire\Admin\DrinkManager;
use App\Livewire\Admin\TransactionLog;
use App\Livewire\Admin\UserManager;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::get('drinks', DrinkManager::class)->name('admin.drinks');
    Route::get('drinkers', DrinkerManager::class)->name('admin.drinkers');
    Route::get('transactions', TransactionLog::class)->name('admin.transactions');
    Route::get('admins', UserManager::class)->name('admin.admins');
});
