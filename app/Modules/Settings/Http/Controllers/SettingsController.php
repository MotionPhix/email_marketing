<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Response;

class SettingsController extends Controller
{
  public function __construct(
    private readonly SettingsService $settings
  ) {}

  public function index(): Response
  {
    $settings = Setting::all()->groupBy('group');

    return inertia('Settings/Index', [
      'settings' => $settings,
      'groups' => [
        'general' => 'General Settings',
        'email' => 'Email Settings',
        'api' => 'API Settings',
        'notification' => 'Notification Settings',
        'appearance' => 'Appearance Settings',
      ],
    ]);
  }

  public function update(Request $request): \Illuminate\Http\RedirectResponse
  {
    $settings = Setting::all();

    foreach ($settings as $setting) {
      if ($request->has($setting->key)) {
        $value = $request->input($setting->key);

        // Validate based on type
        $this->validate($request, [
          $setting->key => $this->getValidationRules($setting),
        ]);

        // Update setting
        $this->settings->set($setting->key, $value);
      }
    }

    return redirect()->back()->with('success', 'Settings updated successfully');
  }

  private function getValidationRules(Setting $setting): array
  {
    $rules = ['required'];

    switch ($setting->type) {
      case 'boolean':
        $rules[] = 'boolean';
        break;
      case 'integer':
        $rules[] = 'integer';
        break;
      case 'float':
        $rules[] = 'numeric';
        break;
      case 'json':
        $rules[] = 'json';
        break;
      case 'array':
        $rules[] = 'array';
        break;
      case 'email':
        $rules[] = 'email';
        break;
      case 'url':
        $rules[] = 'url';
        break;
    }

    if ($setting->options) {
      $rules[] = Rule::in(array_keys($setting->options));
    }

    return $rules;
  }
}
