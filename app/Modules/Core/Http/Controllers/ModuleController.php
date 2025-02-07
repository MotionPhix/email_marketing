<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Inertia\Response;

abstract class ModuleController extends Controller
{
  protected function moduleRender(string $page, array $props = []): Response
  {
    $moduleName = $this->getModuleName();
    return inertia("$moduleName/$page", $props);
  }

  protected function getModuleName(): string
  {
    $className = class_basename(get_called_class());
    $modulePath = (new \ReflectionClass($this))->getNamespaceName();

    if (preg_match('/App\\\\Modules\\\\([^\\\\]+)/', $modulePath, $matches)) {
      return $matches[1];
    }

    return Str::before($className, 'Controller');
  }
}
