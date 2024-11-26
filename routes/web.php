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
      '/send/{campaign:uuid}',
      \App\Http\Controllers\Campaign\Send::class
    )->name('campaigns.send');

    Route::put(
      '/assign/{template:uuid}/{campaign:uuid}',
      \App\Http\Controllers\Campaign\Assign::class
    )->name('campaigns.assign');

  });

  Route::prefix('recipients')->group(function () {

    Route::post(
      '/',
      \App\Http\Controllers\Recipient\Store::class
    )->name('recipients.store');

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

  });

  Route::prefix('templates')->group(function () {

    Route::get(
      '/',
      \App\Http\Controllers\Template\Index::class
    )->name('templates.index');

    Route::get(
      '/c/{campaign:uuid}',
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
      '/{template}',
      \App\Http\Controllers\Template\Update::class
    )->name('templates.update');

    Route::delete(
      '/{template}',
      \App\Http\Controllers\Template\Trash::class
    )->name('templates.destroy');

    Route::post(
      '/p/{template:uuid}',
      \App\Http\Controllers\Template\Preview::class
    )->name('templates.preview');

  });

});

// realtime analytics
Route::post(
  '/analytics',
  \App\Http\Controllers\Hook::class,
)->name('analytics');
