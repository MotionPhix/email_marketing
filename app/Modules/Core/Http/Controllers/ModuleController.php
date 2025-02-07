<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Inertia\Response;
use ReflectionClass;

abstract class ModuleController extends Controller
{
  protected function render(string $component, array $props = []): Response
  {
    return Inertia($component, $props);
  }

  protected function moduleRender(string $page, array $props = []): Response
  {
    $moduleName = $this->getModuleName();
    return inertia("{$moduleName}::{$page}", $props);
  }

  protected function getModuleName(): string
  {
    $reflection = new ReflectionClass($this);
    $namespace = $reflection->getNamespaceName();

    preg_match('/App\\\\Modules\\\\([^\\\\]+)/', $namespace, $matches);
    return $matches[1] ?? '';
  }
}
