<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Models\UserSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class SettingsService
{
  private const CACHE_KEY = 'app_settings';
  private const USER_CACHE_KEY = 'user_settings_';

  public function get(string $key, mixed $default = null): mixed
  {
    // Check user settings first
    if (Auth::check()) {
      $userValue = $this->getUserSetting($key);
      if ($userValue !== null) {
        return $userValue;
      }
    }

    // Then check global settings
    return Cache::remember(self::CACHE_KEY . $key, 3600, function () use ($key, $default) {
      $setting = Setting::where('key', $key)->first();
      return $setting ? $setting->typed_value : $default;
    });
  }

  public function set(string $key, mixed $value, ?string $type = null): void
  {
    $setting = Setting::updateOrCreate(
      ['key' => $key],
      [
        'value' => $value,
        'type' => $type ?? $this->guessType($value),
      ]
    );

    Cache::forget(self::CACHE_KEY . $key);
  }

  public function getUserSetting(string $key): mixed
  {
    $userId = Auth::id();

    return Cache::remember(self::USER_CACHE_KEY . $userId . '_' . $key, 3600, function () use ($key, $userId) {
      $setting = UserSetting::where('user_id', $userId)
        ->where('key', $key)
        ->first();

      return $setting ? $setting->value : null;
    });
  }

  public function setUserSetting(string $key, mixed $value): void
  {
    $userId = Auth::id();

    UserSetting::updateOrCreate(
      [
        'user_id' => $userId,
        'key' => $key,
      ],
      ['value' => $value]
    );

    Cache::forget(self::USER_CACHE_KEY . $userId . '_' . $key);
  }

  public function getAllByGroup(string $group): array
  {
    return Setting::where('group', $group)
      ->get()
      ->mapWithKeys(fn ($setting) => [$setting->key => $setting->typed_value])
      ->toArray();
  }

  private function guessType(mixed $value): string
  {
    return match (true) {
      is_bool($value) => 'boolean',
      is_int($value) => 'integer',
      is_float($value) => 'float',
      is_array($value) => 'json',
      default => 'string',
    };
  }

  public function clearCache(): void
  {
    $settings = Setting::all();
    foreach ($settings as $setting) {
      Cache::forget(self::CACHE_KEY . $setting->key);
    }
  }
}
