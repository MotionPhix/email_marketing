<?php

namespace App\Modules\Core\Support;

use Illuminate\Support\ServiceProvider;

class ModuleService
{
  /**
   * Register all enabled modules
   */
  public function register(): void
  {
    $modules = $this->getEnabledModules();

    foreach ($modules as $module) {
      $this->registerModule($module);
    }
  }

  /**
   * Get all enabled modules from config
   */
  protected function getEnabledModules(): array
  {
    return config('modules.enabled', []);
  }

  /**
   * Register a specific module and its providers
   */
  protected function registerModule(string $module): void
  {
    $moduleClass = config('modules.namespace') . "\\{$module}\\{$module}Module";

    if (class_exists($moduleClass)) {
      $this->registerModuleProviders($moduleClass);
    }
  }

  /**
   * Register all service providers for a module
   */
  protected function registerModuleProviders(string $moduleClass): void
  {
    $providers = $moduleClass::getProviders();

    foreach ($providers as $provider) {
      if (class_exists($provider) && is_subclass_of($provider, ServiceProvider::class)) {
        app()->register($provider);
      }
    }
  }
}
