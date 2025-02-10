<?php

namespace App\Modules\Core\Support;

abstract class BaseModule
{
  /**
   * The module name
   */
  protected static string $name;

  /**
   * Module service providers to register
   */
  protected static array $providers = [];

  /**
   * Get the module name
   */
  public static function getName(): string
  {
    return static::$name ?? class_basename(static::class);
  }

  /**
   * Get the module providers
   */
  public static function getProviders(): array
  {
    return static::$providers;
  }
}
