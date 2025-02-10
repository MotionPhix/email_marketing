<?php

use Illuminate\Support\Facades\Route;

Route::get(
  '/',
  \App\Modules\Home\Http\Controllers\Index::class
)->name('home');
