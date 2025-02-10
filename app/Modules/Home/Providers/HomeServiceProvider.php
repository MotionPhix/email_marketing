<?php

namespace App\Modules\Home\Providers;

use Illuminate\Support\ServiceProvider;

class HomeServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    //
  }

  public function boot(): void
  {
    $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

    if ($this->app->environment('local')) {
      $this->app->make('config')->set('inertia.testing.page_paths', array_merge(
        config('inertia.testing.page_paths', []),
        [
          realpath(__DIR__ . '/../Resources/js/Pages'),
        ]
      ));
    }
  }
}
