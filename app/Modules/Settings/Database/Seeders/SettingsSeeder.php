<?php

namespace App\Modules\Settings\Database\Seeders;

use App\Modules\Settings\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
  public function run(): void
  {
    $settings = [
      // General Settings
      [
        'key' => 'app_name',
        'value' => 'My App',
        'type' => 'string',
        'group' => 'general',
        'label' => 'Application Name',
        'description' => 'The name of your application',
        'is_public' => true,
        'is_system' => false,
      ],
      [
        'key' => 'company_name',
        'value' => 'My Company',
        'type' => 'string',
        'group' => 'general',
        'label' => 'Company Name',
        'description' => 'Your company name',
        'is_public' => true,
        'is_system' => false,
      ],

      // Email Settings
      [
        'key' => 'mail_from_address',
        'value' => 'noreply@example.com',
        'type' => 'email',
        'group' => 'email',
        'label' => 'From Email Address',
        'description' => 'Default email address for sending emails',
        'is_public' => false,
        'is_system' => true,
      ],
      [
        'key' => 'mail_from_name',
        'value' => 'My App',
        'type' => 'string',
        'group' => 'email',
        'label' => 'From Name',
        'description' => 'Default name for sending emails',
        'is_public' => false,
        'is_system' => true,
      ],

      // API Settings
      [
        'key' => 'api_rate_limit',
        'value' => '60',
        'type' => 'integer',
        'group' => 'api',
        'label' => 'API Rate Limit',
        'description' => 'Number of API requests allowed per minute',
        'is_public' => false,
        'is_system' => true,
      ],

      // Notification Settings
      [
        'key' => 'notifications_enabled',
        'value' => '1',
        'type' => 'boolean',
        'group' => 'notification',
        'label' => 'Enable Notifications',
        'description' => 'Enable or disable all notifications',
        'is_public' => false,
        'is_system' => false,
      ],
      [
        'key' => 'notification_email_digest',
        'value' => 'daily',
        'type' => 'string',
        'group' => 'notification',
        'label' => 'Email Digest Frequency',
        'description' => 'How often to send email digests',
        'options' => [
          'never' => 'Never',
          'daily' => 'Daily',
          'weekly' => 'Weekly',
          'monthly' => 'Monthly',
        ],
        'is_public' => false,
        'is_system' => false,
      ],

      // Appearance Settings
      [
        'key' => 'theme',
        'value' => 'light',
        'type' => 'string',
        'group' => 'appearance',
        'label' => 'Theme',
        'description' => 'Application theme',
        'options' => [
          'light' => 'Light',
          'dark' => 'Dark',
          'system' => 'System',
        ],
        'is_public' => true,
        'is_system' => false,
      ],
    ];

    foreach ($settings as $setting) {
      Setting::updateOrCreate(
        ['key' => $setting['key']],
        $setting
      );
    }
  }
}
