<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
  return Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
  ]);
});

Route::middleware([
  'auth:sanctum',
  config('jetstream.auth_session'),
  'verified',
])->group(function () {

  Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
  })->name('dashboard');

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
      '/upload',
      \App\Http\Controllers\Recipient\Upload::class,
    )->name('recipients.upload');

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

    Route::delete(
      '/r/{audience:uuid}/{recipient:uuid}',
      \App\Http\Controllers\Audience\RemoveRecipient::class
    )->name('audiences.remove_recipient');

    Route::get(
      '/a/{audience:uuid}',
      \App\Http\Controllers\Audience\AddRecipient::class
    )->name('audiences.add_recipient');

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
)->name('analytics');
