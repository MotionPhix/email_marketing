<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
    try {
      // Handle provider configuration
      if ($request->has('provider')) {
        $provider = EmailProvider::findOrFail($request->input('provider.id'));

        $emailProviderService = app(EmailProviderService::class);

        if ($emailProviderService->validateProvider(
          $provider,
          $request->input('provider.credentials')
        )) {
          $emailProviderService->setDefaultProvider(
            auth()->id(),
            $provider,
            $request->input('provider.credentials')
          );
        }
      }

      // Handle regular settings
      foreach ($request->input('settings', []) as $key => $value) {
        $this->settings->set($key, $value);
      }

      return back()->with('success', 'Settings updated successfully');
    } catch (ValidationException $e) {
      return back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
      return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
    }
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
