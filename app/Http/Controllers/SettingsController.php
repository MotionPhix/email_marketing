<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class SettingsController extends Controller
{
  /**
   * Main settings dashboard/index
   */
  public function index()
  {
    return Inertia::render('Settings/Index', [
      'settings' => auth()->user()->settings
    ]);
  }

  /**
   * Account and brand settings page
   */
  public function account()
  {
    return Inertia::render('Settings/Account', [
      'settings' => auth()->user()->settings,
    ]);
  }

  /**
   * Handles account and brand settings update
   */
  public function updateAccount(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'sender_settings.default_sender_name' => 'required|string|max:255',
      'sender_settings.default_sender_email' => 'required|email',
      'company_settings.company_name' => 'nullable|string|max:255',
      'company_settings.industry' => 'nullable|string|max:255',
      'company_settings.company_size' => 'nullable|string',
      'company_settings.website' => 'nullable|url',
      'company_settings.phone' => 'nullable|string',
      'company_settings.role' => 'nullable|string|max:255',
      'branding_settings.logo_url' => 'nullable|string',
      'branding_settings.primary_color' => 'nullable|string',
      'branding_settings.accent_color' => 'nullable|string',
    ]);

    if ($validator->fails()) {
      return back()->withErrors($validator);
    }

    $user = auth()->user();

    $user->settings()->update([
      'sender_settings' => array_merge(
        $user->settings->sender_settings ?? [],
        $request->sender_settings
      ),
      'company_settings' => array_merge(
        $user->settings->company_settings ?? [],
        $request->company_settings
      ),
      'branding_settings' => array_merge(
        $user->settings->branding_settings ?? [],
        $request->branding_settings ?? []
      )
    ]);

    return back()->with('success', 'Account settings updated successfully');
  }

  /**
   * Notification and email preferences page
   */
  public function preferences()
  {
    return Inertia::render('Settings/Preferences', [
      'settings' => auth()->user()->settings
    ]);
  }

  /**
   * Handles preferences update
   */
  public function updatePreferences(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'preferences.language' => 'required|string',
      'preferences.timezone' => 'required|string',
      'notification_settings.email_notifications' => 'boolean',
      'notification_settings.in_app_notifications' => 'boolean',
      'marketing_settings.email_updates' => 'boolean',
      'marketing_settings.product_news' => 'boolean',
      'marketing_settings.marketing_communications' => 'boolean',
      'email_settings.track_opens' => 'boolean',
      'email_settings.track_clicks' => 'boolean',
    ]);

    if ($validator->fails()) {
      return back()->withErrors($validator);
    }

    $user = auth()->user();

    $user->settings()->update([
      'preferences' => array_merge(
        $user->settings->preferences ?? [],
        $request->preferences
      ),
      'notification_settings' => array_merge(
        $user->settings->notification_settings ?? [],
        $request->notification_settings
      ),
      'marketing_settings' => array_merge(
        $user->settings->marketing_settings ?? [],
        $request->marketing_settings
      ),
      'email_settings' => array_merge(
        $user->settings->email_settings ?? [],
        $request->email_settings
      )
    ]);

    return back()->with('success', 'Preferences updated successfully');
  }

  /**
   * Verify sender email
   */
  public function verifySenderEmail(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'token' => 'required|string'
    ]);

    if ($validator->fails()) {
      return back()->withErrors($validator);
    }

    $user = auth()->user();

    if ($request->token !== $user->settings->sender_settings['verification_token']) {
      return back()->withErrors(['token' => 'Invalid verification token']);
    }

    $user->settings()->update([
      'sender_settings' => array_merge(
        $user->settings->sender_settings,
        ['email_verified' => true, 'verification_token' => null]
      )
    ]);

    return back()->with('success', 'Email verified successfully');
  }
}
