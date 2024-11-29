<?php

namespace App\Providers;

use App\Services\CampaignEmailService;
use App\Services\TemplateRenderer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->app->singleton(TemplateRenderer::class, function ($app) {
      return new TemplateRenderer();
    });

    $this->app->singleton(CampaignEmailService::class, function ($app) {
      return new CampaignEmailService();
    });
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    //
  }
}
