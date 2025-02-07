<?php

namespace App\Modules\Billing\Providers;

use App\Modules\Billing\Services\BillingService;
use App\Modules\Billing\Services\SubscriptionService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class BillingServiceProvider extends ServiceProvider implements DeferrableProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->mergeConfigFrom(
      __DIR__.'/../Config/config.php', 'billing'
    );

    $this->app->singleton(SubscriptionService::class);
    $this->app->singleton(BillingService::class);
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    if ($this->app->runningInConsole()) {
      $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    $this->registerRoutes();
    $this->registerViews();
  }

  /**
   * Register the module's routes.
   */
  protected function registerRoutes(): void
  {
    Route::middleware('web')
      ->group(fn () => require __DIR__.'/../Routes/web.php');

    Route::prefix('api')
      ->middleware('api')
      ->group(fn () => require __DIR__.'/../Routes/api.php');
  }

  /**
   * Register the module's views.
   */
  protected function registerViews(): void
  {
    $this->loadViewsFrom(__DIR__.'/../Resources/views', 'billing');
  }

  /**
   * Get the services provided by the provider.
   *
   * @return array<int, string>
   */
  public function provides(): array
  {
    return [
      BillingService::class,
      SubscriptionService::class,
    ];
  }
}
