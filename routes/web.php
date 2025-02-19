<?php

use App\Http\Controllers\Api\V1\BrandingController;
use App\Http\Controllers\Api\V1\QuotaController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\TeamMemberController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return redirect()->route('settings.account');
});

Route::middleware(['auth'])->group(function () {
  // Dashboard
  Route::get(
    '/dashboard',
    \App\Http\Controllers\Analytics::class
  )->name('dashboard');

  // Onboarding Routes
  Route::prefix('onboarding')->group(function () {

    Route::get(
      '/',
      [OnboardingController::class, 'index']
    )->name('onboarding.index');

    Route::post(
      '/update-step',
      [OnboardingController::class, 'updateStep']
    )->name('onboarding.update-step');

    Route::post(
      '/complete',
      [OnboardingController::class, 'completeOnboarding']
    )->name('onboarding.complete');

    Route::post(
      '/skip-onboarding',
      [OnboardingController::class, 'skip']
    )->name('onboarding.skip');

    Route::post(
      '/skip-current-step/{step}',
      [OnboardingController::class, 'skipCurrent']
    )->name('onboarding.skip-current');

  });

  Route::get('templates', [EmailTemplateController::class, 'index'])->name('templates.index');
  Route::get('templates/create', [EmailTemplateController::class, 'create'])->name('templates.create');
  Route::post('templates', [EmailTemplateController::class, 'store'])->name('templates.store');
  Route::get('templates/{template}/edit', [EmailTemplateController::class, 'edit'])->name('templates.edit');
  Route::put('templates/{template}', [EmailTemplateController::class, 'update'])->name('templates.update');
  Route::delete('templates/{template}', [EmailTemplateController::class, 'destroy'])->name('templates.destroy');
  Route::post('templates/{template}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('templates.duplicate');
  Route::get('templates/{template}/preview', [EmailTemplateController::class, 'preview'])->name('templates.preview');
  Route::get('template-variables', [EmailTemplateController::class, 'variables'])->name('templates.variables');

  Route::prefix('campaigns')
    ->name('campaigns.')
    ->middleware('onboarding.complete')
    ->group(function () {

      Route::get(
        '/',
        [CampaignController::class, 'index']
      )->name('index');

      Route::get(
        '/new-campaign',
        [CampaignController::class, 'create']
      )->name('create');

      Route::post(
        '/',
        [CampaignController::class, 'store']
      )->name('store');

      Route::get(
        '/s/{campaign}',
        [CampaignController::class, 'show']
      )->name('show');

      Route::get(
        '/e/{campaign}',
        [CampaignController::class, 'edit']
      )->name('edit');

      Route::put(
        '/u/{campaign}',
        [CampaignController::class, 'update']
      )->name('update');

      Route::delete(
        '/d/{campaign}',
        [CampaignController::class, 'destroy']
      )->name('destroy');

      // Index Actions
      Route::post(
        '/schedule/{campaign}',
        [CampaignController::class, 'schedule']
      )->name('schedule');

      Route::post(
        '/send/{campaign}',
        [CampaignController::class, 'send']
      )->name('send');

      Route::post(
        '/duplicate/{campaign}',
        [CampaignController::class, 'duplicate']
      )->name('duplicate');

      Route::get(
        '/preview/{campaign}',
        [CampaignController::class, 'preview']
      )->name('preview');

      // Index Stats
      Route::get(
        '/stats/{campaign}',
        [CampaignController::class, 'stats']
      )->name('stats');

      Route::delete(
        '/bulk-delete',
        [CampaignController::class, 'bulkDelete']
      )->name('bulk-delete');

      Route::get(
        '/export',
        [CampaignController::class, 'export']
      )->name('export');

    });

  // Subscriber routes
  Route::prefix('subscribers')->name('subscribers.')->middleware(['auth'])->group(function () {
    Route::get('/', [SubscriberController::class, 'index'])->name('index');
    Route::get('/new-subscriber', [SubscriberController::class, 'create'])->name('create');
    Route::post('/', [SubscriberController::class, 'store'])->name('store');
    Route::get('/e/{subscriber}', [SubscriberController::class, 'edit'])->name('edit');
    Route::put('/u/{subscriber}', [SubscriberController::class, 'update'])->name('update');
    Route::get('/s/{subscriber}', [SubscriberController::class, 'show'])->name('show');
    Route::delete('/d/{subscriber}', [SubscriberController::class, 'destroy'])->name('destroy');

    // Import and Export routes
    Route::post('/import', [SubscriberController::class, 'import'])->name('import');
    Route::get('/export', [SubscriberController::class, 'export'])->name('export');

    // Bulk actions
    Route::post('/bulk-destroy', [SubscriberController::class, 'bulkDestroy'])->name('bulk-destroy');
    Route::post('/bulk-update', [SubscriberController::class, 'bulkUpdate'])->name('bulk-update');

  });

  // Team Management
  Route::prefix('teams')->group(function () {
    Route::get('/', [TeamController::class, 'index']);
    Route::post('/', [TeamController::class, 'store']);
    Route::get('/{team}', [TeamController::class, 'show']);
    Route::put('/{team}', [TeamController::class, 'update']);
    Route::delete('/{team}', [TeamController::class, 'destroy']);

    // Team Members
    Route::get('/{team}/members', [TeamMemberController::class, 'index']);
    Route::post('/{team}/members', [TeamMemberController::class, 'store']);
    Route::put('/{team}/members/{user}', [TeamMemberController::class, 'update']);
    Route::delete('/{team}/members/{user}', [TeamMemberController::class, 'destroy']);

    // Team Invitations
    Route::get('/{team}/invitations', [TeamInvitationController::class, 'index']);
    Route::post('/{team}/invitations', [TeamInvitationController::class, 'store']);
    Route::delete('/{team}/invitations/{invitation}', [TeamInvitationController::class, 'destroy']);
  });

  /*// API Keys
  Route::prefix('api-keys')->group(function () {
    Route::get('/', [ApiKeyController::class, 'index']);
    Route::post('/', [ApiKeyController::class, 'store']);
    Route::delete('/{apiKey}', [ApiKeyController::class, 'destroy']);
  });*/

  // Branding
  Route::prefix('branding')->group(function () {
    Route::get('/', [BrandingController::class, 'show']);
    Route::put('/', [BrandingController::class, 'update']);
    Route::post('/logo', [BrandingController::class, 'uploadLogo']);
  });

  // Email Quotas
  Route::prefix('quotas')->group(function () {
    Route::get('/', [QuotaController::class, 'show']);
    Route::get('/usage', [QuotaController::class, 'usage']);
  });

  Route::prefix('settings')->name('settings.')->middleware(['auth'])->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');

    // Account & Brand Settings
    Route::get('/account', [SettingsController::class, 'account'])->name('account');
    Route::post('/account', [SettingsController::class, 'updateAccount'])->name('account.update');

    // Preferences & Notifications
    Route::get('/preferences', [SettingsController::class, 'preferences'])->name('preferences');
    Route::post('/preferences', [SettingsController::class, 'updatePreferences'])->name('preferences.update');

    // Email Verification
    Route::post('/verify-sender', [SettingsController::class, 'verifySenderEmail'])->name('verify-sender');
  });
});

Route::get('/team-invitations/{token}', [TeamInvitationController::class, 'accept'])
  ->name('team-invitations.accept');

Route::post('/team-invitations/{token}/register', [TeamInvitationController::class, 'register'])
  ->name('team-invitations.register');

Route::patch(
  'unsubscribe/{subscriber}',
  [SubscriberController::class, 'unsubscribe']
)->name('subscribers.unsubscribe');

Route::post(
  'webhooks/sendgrid',
  [WebhookController::class, 'handleSendGridEvents']
)->name('webhooks.sendgrid');
