<?php

namespace App\Modules\Home;

use App\Modules\Core\Support\BaseModule;

class HomeModule extends BaseModule
{
  protected static string $name = 'Home';

  protected static array $providers = [
    \App\Modules\Home\Providers\HomeServiceProvider::class,
  ];
}
