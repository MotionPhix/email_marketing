<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
  return Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'plans' => \App\Models\Plan::all(),
  ]);
})->name('home');

Route::middleware([
  'auth:sanctum',
  config('jetstream.auth_session'),
  'verified',
])->group(function () {

  Route::get(
    '/dashboard',
    \App\Http\Controllers\Analytics::class,
  )->name('dashboard');

  Route::prefix('settings')->group(function () {

    Route::get(
      '/',
      \App\Http\Controllers\Setting\Setup::class,
    )->name('settings.index');

    Route::post(
      '/s',
      \App\Http\Controllers\Setting\Store::class,
    )->name('settings.store');

    Route::patch(
      '/p/{setting:uuid}/p/{plan:uuid}',
      \App\Http\Controllers\Setting\Payment::class,
    )->name('settings.payment');

    Route::put(
      '/u/{setting:uuid}',
      \App\Http\Controllers\Setting\Update::class,
    )->name('settings.update');

  });

  Route::prefix('campaigns')->group(function () {

    Route::get(
      '/',
      \App\Http\Controllers\Campaign\Index::class
    )->name('campaigns.index');

    Route::get(
      '/c/{campaign:uuid?}',
      \App\Http\Controllers\Campaign\Form::class
    )->name('campaigns.create');

    Route::get(
      '/s/{campaign:uuid}',
      \App\Http\Controllers\Campaign\show::class
    )->name('campaigns.show');

    Route::post(
      '/',
      \App\Http\Controllers\Campaign\Store::class
    )->name('campaigns.store');

    Route::put(
      '/{campaign:uuid}',
      \App\Http\Controllers\Campaign\Update::class
    )->name('campaigns.update');

    Route::post(
      '/send/{campaign:uuid?}',
      \App\Http\Controllers\Campaign\Send::class
    )->name('campaigns.send');

    Route::get(
      '/e/{campaign:uuid}',
      \App\Http\Controllers\Campaign\Form::class
    )->name('campaigns.edit');

    Route::put(
      '/a/{template:uuid}/{campaign:uuid}',
      \App\Http\Controllers\Campaign\Assign::class
    )->name('campaigns.assign');

    Route::get(
      '/schedule/{campaign:uuid}',
      \App\Http\Controllers\Campaign\Schedule::class
    )->name('campaigns.schedule');

    Route::put(
      '/schedule/{campaign:uuid}',
      \App\Http\Controllers\Campaign\Console::class
    )->name('campaigns.console');

    Route::get(
      '/unsubscribe/{campaign:uuid}/{recipient:uuid}',
      \App\Http\Controllers\Campaign\Unsubscribe::class
    )->name('campaigns.unsubscribe');

  });

  Route::prefix('recipients')->group(function () {

    Route::get(
      '/',
      \App\Http\Controllers\Recipient\Index::class
    )->name('recipients.index');

    Route::post(
      '/',
      \App\Http\Controllers\Recipient\Store::class
    )->name('recipients.store');

    Route::get(
      '/c',
      \App\Http\Controllers\Recipient\Form::class
    )->name('recipients.create');

    Route::get(
      '/e/{recipient:uuid}',
      \App\Http\Controllers\Recipient\Form::class
    )->name('recipients.edit');

    Route::put(
      '/u/{recipient:uuid}',
      \App\Http\Controllers\Recipient\Update::class
    )->name('recipients.update');

    Route::post(
      '/i',
      \App\Http\Controllers\Recipient\Import::class,
    )->name('recipients.import.store');

    Route::get(
      '/i',
      \App\Http\Controllers\Recipient\Upload::class,
    )->name('recipients.import');

    Route::get(
      '/b/{action}/{recipients}',
      \App\Http\Controllers\Recipient\BatchHandler::class,
    )->name('recipients.batch');

    Route::get(
      '/s/{recipient:uuid}',
      \App\Http\Controllers\Recipient\Show::class,
    )->name('recipients.show');

  });

  Route::prefix('audiences')->group(function () {

    Route::get(
      '/',
      \App\Http\Controllers\Audience\Index::class
    )->name('audiences.index');

    Route::post(
      '/',
      \App\Http\Controllers\Audience\Store::class
    )->name('audiences.store');

    Route::get(
      '/a/{audience:uuid}',
      \App\Http\Controllers\Audience\AddRecipient::class
    )->name('audiences.add_recipient');

    Route::delete(
      '/r/{audience:uuid}/{recipient:uuid}',
      \App\Http\Controllers\Audience\RemoveRecipient::class
    )->name('audiences.remove_recipient');

    Route::put(
      '/m/{audience:uuid}',
      \App\Http\Controllers\Audience\MergeRecipients::class
    )->name('audiences.merge_recipients');

    Route::get(
      '/audience/{audience:uuid}',
      \App\Http\Controllers\Audience\Show::class
    )->name('audiences.show');

    Route::get(
      '/c',
      \App\Http\Controllers\Audience\Form::class
    )->name('audiences.create');

    Route::get(
      '/e/{audience:uuid}',
      \App\Http\Controllers\Audience\Form::class
    )->name('audiences.edit');

    Route::put(
      '/u/{audience:uuid}',
      \App\Http\Controllers\Audience\Update::class
    )->name('audiences.update');

    Route::delete(
      '/u/{audience:uuid}',
      \App\Http\Controllers\Audience\Trash::class
    )->name('audiences.destroy');

  });

  Route::prefix('templates')->group(function () {

    Route::get(
      '/',
      \App\Http\Controllers\Template\Index::class
    )->name('templates.index');

    Route::get(
      '/c/{campaign:uuid?}',
      \App\Http\Controllers\Template\Form::class
    )->name('templates.create');

    Route::post(
      '/',
      \App\Http\Controllers\Template\Store::class
    )->name('templates.store');

    Route::get(
      '/e/{template:uuid}',
      \App\Http\Controllers\Template\Form::class
    )->name('templates.edit');

    Route::get(
      '/p/{template:uuid}',
      \App\Http\Controllers\Template\Preview::class
    )->name('templates.preview');

    Route::put(
      '/u/{template:uuid}',
      \App\Http\Controllers\Template\Update::class
    )->name('templates.update');

    Route::get(
      '/p/{template}',
      \App\Http\Controllers\Template\Preview::class
    )->name('templates.preview');

    Route::delete(
      '/d/{template:uuid}',
      \App\Http\Controllers\Template\Trash::class
    )->name('templates.destroy');

  });

});

// realtime analytics
Route::post(
  '/analytics',
  \App\Http\Controllers\Hook::class,
)->middleware(['throttle:60,1'])
  ->name('analytics');
