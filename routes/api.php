<?php

use App\Http\Controllers\Api\MailingListApiController;
use App\Http\Controllers\Api\SegmentApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get(
  '/campaigns/{campaignId}/stats',
  [\App\Http\Controllers\Api\Campaign\Stats::class, 'show']
)->middleware('auth:sanctum')->name('campaign.stats');

Route::middleware(['auth:sanctum'])->group(function () {
  // Mailing Lists API routes
  Route::prefix('mailing-lists')->name('api.mailing-lists.')->group(function () {
    Route::get('/', [MailingListApiController::class, 'index'])
      ->name('index');
    Route::get('{mailingList}', [MailingListApiController::class, 'show'])
      ->name('show');
    Route::get('{mailingList}/subscribers', [MailingListApiController::class, 'subscribers'])
      ->name('subscribers');
  });

  // Segments API routes
  Route::prefix('segments')->name('api.segments.')->group(function () {
    Route::get('/', [SegmentApiController::class, 'index'])
      ->name('index');
    Route::get('{segment}', [SegmentApiController::class, 'show'])
      ->name('show');
    Route::get('{segment}/preview', [SegmentApiController::class, 'preview'])
      ->name('preview');
  });
});
