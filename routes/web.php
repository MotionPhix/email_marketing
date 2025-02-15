<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
  // Onboarding Routes
  Route::get(
    '/onboarding',
    [OnboardingController::class, 'index']
  )->name('onboarding.index');

  Route::post(
    '/onboarding/update-step',
    [OnboardingController::class, 'updateStep']
  )->name('onboarding.update-step');

  Route::post(
    '/onboarding/complete',
    [OnboardingController::class, 'completeOnboarding']
  )->name('onboarding.complete');

  Route::post(
    '/onboarding/skip',
    [OnboardingController::class, 'skip']
  )->name('onboarding.skip');

  Route::get('templates', [EmailTemplateController::class, 'index'])->name('templates.index');
  Route::get('templates/create', [EmailTemplateController::class, 'create'])->name('templates.create');
  Route::post('templates', [EmailTemplateController::class, 'store'])->name('templates.store');
  Route::get('templates/{template}/edit', [EmailTemplateController::class, 'edit'])->name('templates.edit');
  Route::put('templates/{template}', [EmailTemplateController::class, 'update'])->name('templates.update');
  Route::delete('templates/{template}', [EmailTemplateController::class, 'destroy'])->name('templates.destroy');
  Route::post('templates/{template}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('templates.duplicate');
  Route::get('templates/{template}/preview', [EmailTemplateController::class, 'preview'])->name('templates.preview');
  Route::get('template-variables', [EmailTemplateController::class, 'variables'])->name('templates.variables');

  Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
  Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
  Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
  Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
  Route::get('/campaigns/{campaign}/edit', [CampaignController::class, 'edit'])->name('campaigns.edit');
  Route::put('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
  Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');

  // Campaign Actions
  Route::post('/campaigns/{campaign}/schedule', [CampaignController::class, 'schedule'])->name('campaigns.schedule');
  Route::post('/campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
  Route::post('/campaigns/{campaign}/duplicate', [CampaignController::class, 'duplicate'])->name('campaigns.duplicate');
  Route::get('/campaigns/{campaign}/preview', [CampaignController::class, 'preview'])->name('campaigns.preview');

  // Campaign Stats
  Route::get('/campaigns/{campaign}/stats', [CampaignController::class, 'stats'])->name('campaigns.stats');

  // Subscriber routes
  Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
  Route::get('subscribers/create', [SubscriberController::class, 'create'])->name('subscribers.create');
});
