<?php

use App\Modules\Billing\Http\Controllers\PayChanguWebhook;
use App\Modules\Billing\Http\Controllers\BillingController;
use App\Modules\Billing\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/paychangu', [PayChanguWebhook::class, 'handle'])
  ->name('webhooks.paychangu');

Route::get('/debug-vite', function() {
  $pages = glob(base_path('app/Modules/*/Resources/js/Pages/**/*.vue'));
  return [
    'module_pages' => $pages,
    'app_pages' => glob(resource_path('js/Pages/**/*.vue')),
  ];
});

Route::middleware(['auth'])->group(function () {
  Route::get('/pay-changu-billing', [BillingController::class, 'index'])->name('billing.index');

  Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
    Route::post('/', [SubscriptionController::class, 'store'])->name('store');
    Route::delete('/{uuid}', [SubscriptionController::class, 'destroy'])->name('destroy');
    Route::get('/success', [SubscriptionController::class, 'success'])->name('success');
    Route::get('/cancelled', [SubscriptionController::class, 'cancelled'])->name('cancelled');
  });
});
