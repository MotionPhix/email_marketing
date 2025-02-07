<?php

use App\Modules\Campaigns\Http\Controllers\CampaignController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
  Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
  Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
  Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
  Route::get('/campaigns/{campaign}', [CampaignController::class, 'edit'])->name('campaigns.edit');
  Route::put('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
  Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');
  Route::post('/campaigns/{campaign}/schedule', [CampaignController::class, 'schedule'])->name('campaigns.schedule');
  Route::post('/campaigns/{campaign}/cancel', [CampaignController::class, 'cancel'])->name('campaigns.cancel');
  Route::get('/campaigns/{campaign}/preview', [CampaignController::class, 'preview'])->name('campaigns.preview');
});
