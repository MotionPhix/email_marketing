<?php

use App\Http\Controllers\BillingController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get(
  '/',
  \App\Http\Controllers\Home\Index::class,
)->name('home');

// Authentication required routes
Route::middleware([
  'auth:sanctum',
  config('jetstream.auth_session'),
  'verified',
])->group(function () {

  // Routes that don't require subscription check
  Route::group([], function () {

    // Dashboard
    Route::get(
      '/dashboard',
      \App\Http\Controllers\Analytics::class
    )->name('dashboard');

    // Settings
    Route::prefix('settings')->group(function () {
      Route::get('/', \App\Http\Controllers\Setting\Setup::class)
        ->name('settings.index');
      Route::post('/s', \App\Http\Controllers\Setting\Store::class)
        ->name('settings.store');
      Route::patch('/p/{setting:uuid}/p/{plan:uuid}', \App\Http\Controllers\Setting\Payment::class)
        ->name('settings.payment');
      Route::put('/u/{setting:uuid}', \App\Http\Controllers\Setting\Update::class)
        ->name('settings.update');
    });

    // Billing & Subscription Management
    Route::prefix('billing')->group(function () {
      Route::get(
        '/',
        [BillingController::class, 'index']
      )->name('billing');

      Route::patch(
        '/auto-renew',
        [BillingController::class, 'toggleAutoRenew']
      )->name('subscription.auto-renew');

      Route::post(
        '/change-plan',
        [BillingController::class, 'changePlan']
      )->name('subscription.change-plan');

      Route::get(
        '/invoice/{reference?}',
        [BillingController::class, 'downloadInvoice']
      )->name('billing.invoice');


      Route::post('/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
      Route::delete('/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
      Route::post('/upgrade', [BillingController::class, 'upgrade'])->name('billing.upgrade');

    });

    // Subscription routes
    Route::prefix('subscription')->group(function () {
      Route::post('/s/{plan}', [\App\Http\Controllers\Payment\SubscriptionController::class, 'subscribe'])
        ->name('subscription.create');
      Route::get('/p/callback', [\App\Http\Controllers\Payment\SubscriptionController::class, 'callback'])
        ->name('subscription.callback');
      Route::post('/r/callback/{subscription}', [\App\Http\Controllers\Payment\SubscriptionController::class, 'handleRenewalCallback'])
        ->name('subscription.renewal.callback');
    });

    // Notifications
    Route::patch('notifications/{id}/read', function ($id) {
      auth()->user()->notifications()->findOrFail($id)->markAsRead();
      return back();
    })->name('notifications.mark-as-read');

  });

  // Routes that require subscription check
  Route::middleware('subscription')->group(function () {
    // Campaigns
    Route::prefix('campaigns')->group(function () {
      Route::get('/', \App\Http\Controllers\Campaign\Index::class)
        ->name('campaigns.index');
      Route::get('/c/{campaign:uuid?}', \App\Http\Controllers\Campaign\Form::class)
        ->name('campaigns.create');
      Route::get('/s/{campaign:uuid}', \App\Http\Controllers\Campaign\show::class)
        ->name('campaigns.show');
      Route::post('/', \App\Http\Controllers\Campaign\Store::class)
        ->name('campaigns.store');
      Route::put('/{campaign:uuid}', \App\Http\Controllers\Campaign\Update::class)
        ->name('campaigns.update');
      Route::post('/send/{campaign:uuid?}', \App\Http\Controllers\Campaign\Send::class)
        ->name('campaigns.send');
      Route::get('/e/{campaign:uuid}', \App\Http\Controllers\Campaign\Form::class)
        ->name('campaigns.edit');
      Route::put('/a/{template:uuid}/{campaign:uuid}', \App\Http\Controllers\Campaign\Assign::class)
        ->name('campaigns.assign');
      Route::get('/schedule/{campaign:uuid}', \App\Http\Controllers\Campaign\Schedule::class)
        ->name('campaigns.schedule');
      Route::put('/schedule/{campaign:uuid}', \App\Http\Controllers\Campaign\Console::class)
        ->name('campaigns.console');
      Route::post('/cancel-schedule/{campaign:uuid}', \App\Http\Controllers\Campaign\CancelSchedule::class)
        ->name('campaigns.cancel_schedule');
    });

    // Recipients
    Route::prefix('recipients')->group(function () {
      Route::get('/', \App\Http\Controllers\Recipient\Index::class)
        ->name('recipients.index');
      Route::post('/', \App\Http\Controllers\Recipient\Store::class)
        ->name('recipients.store');
      Route::get('/c', \App\Http\Controllers\Recipient\Form::class)
        ->name('recipients.create');
      Route::get('/e/{recipient:uuid}', \App\Http\Controllers\Recipient\Form::class)
        ->name('recipients.edit');
      Route::put('/u/{recipient:uuid}', \App\Http\Controllers\Recipient\Update::class)
        ->name('recipients.update');
      Route::post('/i', \App\Http\Controllers\Recipient\Import::class)
        ->name('recipients.import.store');
      Route::get('/i', \App\Http\Controllers\Recipient\Upload::class)
        ->name('recipients.import');
      Route::get('/b/{action}/{recipients}', \App\Http\Controllers\Recipient\BatchHandler::class)
        ->name('recipients.batch');
      Route::get('/s/{recipient:uuid}', \App\Http\Controllers\Recipient\Show::class)
        ->name('recipients.show');
    });

    // Templates
    Route::prefix('templates')->group(function () {
      Route::get('/', \App\Http\Controllers\Template\Index::class)
        ->name('templates.index');
      Route::get('/c/{campaign:uuid?}', \App\Http\Controllers\Template\Form::class)
        ->name('templates.create');
      Route::post('/', \App\Http\Controllers\Template\Store::class)
        ->name('templates.store');
      Route::get('/e/{template:uuid}', \App\Http\Controllers\Template\Form::class)
        ->name('templates.edit');
      Route::get('/p/{template:uuid}', \App\Http\Controllers\Template\Preview::class)
        ->name('templates.preview');
      Route::put('/u/{template:uuid}', \App\Http\Controllers\Template\Update::class)
        ->name('templates.update');
      Route::delete('/d/{template:uuid}', \App\Http\Controllers\Template\Trash::class)
        ->name('templates.destroy');
    });
  });
});

// Public webhook routes
Route::post(
  '/analytics',
  \App\Http\Controllers\Hook::class
)->middleware(['throttle:60,1'])
  ->name('analytics');

Route::post(
  'webhooks/paychangu',
  [\App\Modules\Billing\Http\Controllers\PayChanguWebhook::class, 'handle']
)->name('webhooks.paychangu');

// Unsubscribe route (should be public)
Route::get('/campaigns/unsubscribe/{campaign:uuid}/{recipient:uuid}',
  \App\Http\Controllers\Campaign\Unsubscribe::class)
  ->name('campaigns.unsubscribe');

require_once __DIR__ . '/fortify.php';
require_once __DIR__ . '/jetstream.php';
