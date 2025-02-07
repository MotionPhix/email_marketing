<?php

namespace App\Modules\Core\Support;

use Illuminate\Support\Facades\File;

class ModuleLoader
{
  protected array $modules = [];

  public function boot(): void
  {
    $this->modules = $this->getModules();

    foreach ($this->modules as $module) {
      $this->registerModule($module);
    }
  }

  protected function getModules(): array
  {
    $modules = [];
    $modulePath = app_path('Modules');

    if (!File::exists($modulePath)) {
      return $modules;
    }

    $directories = File::directories($modulePath);

    foreach ($directories as $directory) {
      if ($directory !== app_path('Modules/Core')) {
        $modules[] = basename($directory);
      }
    }

    return $modules;
  }

  protected function registerModule(string $module): void
  {
    // Register routes
    $this->registerRoutes($module);

    // Register provider
    $this->registerProvider($module);

    // Register migrations
    $this->registerMigrations($module);
  }

  protected function registerRoutes(string $module): void
  {
    $webRoute = app_path("Modules/{$module}/Routes/web.php");
    $apiRoute = app_path("Modules/{$module}/Routes/api.php");

    if (File::exists($webRoute)) {
      require $webRoute;
    }

    if (File::exists($apiRoute)) {
      require $apiRoute;
    }
  }

  protected function registerProvider(string $module): void
  {
    $provider = "App\\Modules\\{$module}\\Providers\\{$module}ServiceProvider";

    if (class_exists($provider)) {
      app()->register($provider);
    }
  }

  protected function registerMigrations(string $module): void
  {
    $migrationPath = app_path("Modules/{$module}/Database/Migrations");

    if (File::exists($migrationPath)) {
      $paths = app('migrator')->paths();

      // Add module migrations path if not already included
      if (!in_array($migrationPath, $paths)) {
        app('migrator')->path($migrationPath);
      }
    }
  }
}
