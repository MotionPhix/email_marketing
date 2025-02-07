<?php

namespace App\Modules\Core\Providers;

use App\Modules\Core\Support\ModuleLoader;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->app->singleton(ModuleLoader::class);
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    $this->app->make(ModuleLoader::class)->boot();
  }
}
