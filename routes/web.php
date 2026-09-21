<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Kiosk\Activity;
use App\Livewire\Kiosk\DrinkerHistory;
use App\Livewire\Kiosk\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('kiosk.home');
Route::get('/activity', Activity::class)->name('kiosk.activity');
Route::get('/drinkers/{drinker}', DrinkerHistory::class)->name('kiosk.drinker-history');

Route::get('dashboard', Dashboard::class)->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
