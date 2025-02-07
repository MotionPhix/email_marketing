<?php

use App\Modules\Campaigns\Http\Controllers\CampaignController;
use App\Modules\Campaigns\Http\Controllers\CampaignTrackingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

  Route::prefix('campaigns')->group(function () {

    Route::post(
      '/send/{campaign}/send-now',
      [CampaignController::class, 'sendNow']
    )->name('campaigns.send-now');

    Route::get(
      '/i/campaigns',
      [CampaignController::class, 'index']
    )->name('campaigns.index');

    Route::get(
      '/c/new-campaign',
      [CampaignController::class, 'create']
    )->name('campaigns.create');

    Route::post(
      '/s/campaign',
      [CampaignController::class, 'store']
    )->name('campaigns.store');

    Route::get(
      '/e/{campaign}',
      [CampaignController::class, 'edit']
    )->name('campaigns.edit');

    Route::put(
      '/u/{campaign}',
      [CampaignController::class, 'update']
    )->name('campaigns.update');

    Route::delete(
      '/d/{campaign}',
      [CampaignController::class, 'destroy']
    )->name('campaigns.destroy');

    Route::post(
      '/schedule/{campaign}',
      [CampaignController::class, 'schedule']
    )->name('campaigns.schedule');

    Route::post(
      '/cancel/{campaign}',
      [CampaignController::class, 'cancel']
    )->name('campaigns.cancel');

    Route::get(
      '/preview/{campaign}',
      [CampaignController::class, 'preview']
    )->name('campaigns.preview');

  });

});

Route::prefix('campaigns/track')
  ->name('campaigns.track.')
  ->group(function () {

    Route::get(
      '{campaign}/{recipient}/open',
      [CampaignTrackingController::class, 'trackOpen']
    )->name('open');

    Route::get(
      '{campaign}/{recipient}/click/{url}',
      [CampaignTrackingController::class, 'trackClick']
    )->name('click');

  });
