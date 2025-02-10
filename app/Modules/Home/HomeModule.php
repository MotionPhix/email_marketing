<?php

namespace App\Modules\Home;

use App\Modules\Core\Support\BaseModule;
use App\Modules\Home\Providers\HomeServiceProvider;

class HomeModule extends BaseModule
{
  protected static string $name = 'js';

  protected static array $providers = [
    HomeServiceProvider::class,
  ];
}
