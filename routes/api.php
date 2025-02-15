<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get(
  '/campaigns/{campaignId}/stats',
  [\App\Http\Controllers\Api\Campaign\Stats::class, 'show']
)->middleware('auth:sanctum')->name('campaign.stats');
