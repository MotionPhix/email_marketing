<?php

use App\Modules\Settings\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
  Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
  Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
