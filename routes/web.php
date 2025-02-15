<?php

use App\Http\Controllers\EmailTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
  Route::get('templates', [EmailTemplateController::class, 'index'])->name('templates.index');
  Route::get('templates/create', [EmailTemplateController::class, 'create'])->name('templates.create');
  Route::post('templates', [EmailTemplateController::class, 'store'])->name('templates.store');
  Route::get('templates/{template}/edit', [EmailTemplateController::class, 'edit'])->name('templates.edit');
  Route::put('templates/{template}', [EmailTemplateController::class, 'update'])->name('templates.update');
  Route::delete('templates/{template}', [EmailTemplateController::class, 'destroy'])->name('templates.destroy');
  Route::post('templates/{template}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('templates.duplicate');
  Route::get('templates/{template}/preview', [EmailTemplateController::class, 'preview'])->name('templates.preview');
  Route::get('template-variables', [EmailTemplateController::class, 'variables'])->name('templates.variables');
});

require_once __DIR__ . '/fortify.php';
require_once __DIR__ . '/jetstream.php';
