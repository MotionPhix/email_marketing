<?php

namespace App\Modules\Core\Providers;

use App\Modules\Core\Support\ModuleService;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->app->singleton(ModuleService::class);

    // Register the modules configuration
    $this->mergeConfigFrom(
      base_path('config/modules.php'), 'modules'
    );
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    $this->app->make(ModuleService::class)->register();
  }
}
