<?php

namespace App\Modules\Settings\Database\Seeders;

use App\Modules\Settings\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
  public function run(): void
  {
    // Seed Email Providers
    $emailProviders = [
      [
        'name' => 'SendGrid',
        'slug' => 'sendgrid',
        'description' => 'SendGrid email service by Twilio',
        'required_fields' => [
          'api_key' => [
            'type' => 'password',
            'label' => 'API Key',
            'required' => true
          ]
        ]
      ],
      [
        'name' => 'Mailtrap',
        'slug' => 'mailtrap',
        'description' => 'Email delivery platform for development teams',
        'required_fields' => [
          'username' => [
            'type' => 'string',
            'label' => 'Username',
            'required' => true
          ],
          'password' => [
            'type' => 'password',
            'label' => 'Password',
            'required' => true
          ]
        ]
      ],
      // Add more providers as needed
    ];

    foreach ($emailProviders as $provider) {
      EmailProvider::updateOrCreate(
        ['slug' => $provider['slug']],
        $provider
      );
    }

    // Seed Settings
    $settings = [
      // General Settings
      [
        'key' => 'app_name',
        'value' => 'My Email Marketing App',
        'type' => 'string',
        'group' => 'general',
        'label' => 'Application Name',
        'description' => 'The name of your application',
        'is_public' => true,
      ],

      // Locale Settings
      [
        'key' => 'timezone',
        'value' => 'UTC',
        'type' => 'string',
        'group' => 'locale',
        'label' => 'Timezone',
        'description' => 'Your preferred timezone',
        'options' => array_combine(
          timezone_identifiers_list(),
          timezone_identifiers_list()
        ),
        'is_public' => true,
      ],
      [
        'key' => 'currency',
        'value' => 'USD',
        'type' => 'string',
        'group' => 'locale',
        'label' => 'Currency',
        'description' => 'Your preferred currency',
        'options' => [
          'USD' => 'US Dollar ($)',
          'EUR' => 'Euro (€)',
          'GBP' => 'British Pound (£)',
          // Add more currencies
        ],
        'is_public' => true,
      ],

      // Email Settings
      [
        'key' => 'mail_from_name',
        'value' => 'My App',
        'type' => 'string',
        'group' => 'email',
        'label' => 'From Name',
        'description' => 'Default name for sending emails',
        'is_public' => false,
      ],
      [
        'key' => 'mail_from_address',
        'value' => 'noreply@example.com',
        'type' => 'email',
        'group' => 'email',
        'label' => 'From Email',
        'description' => 'Default email address for sending emails',
        'is_public' => false,
      ],
      [
        'key' => 'mail_reply_to',
        'value' => null,
        'type' => 'email',
        'group' => 'email',
        'label' => 'Reply-To Email',
        'description' => 'Default reply-to email address',
        'is_public' => false,
      ],
      [
        'key' => 'default_email_provider',
        'value' => null,
        'type' => 'string',
        'group' => 'email',
        'label' => 'Default Email Provider',
        'description' => 'Your preferred email delivery service',
        'options' => EmailProvider::pluck('name', 'slug')->toArray(),
        'is_public' => false,
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
