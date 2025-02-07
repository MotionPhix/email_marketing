<?php

return [
  'grace_period' => env('BILLING_GRACE_PERIOD', 3), // days
  'trial_days' => env('BILLING_TRIAL_DAYS', 14),
  'default_currency' => env('BILLING_CURRENCY', 'MWK'),

  'paychangu' => [
    'base_url' => env('PAYCHANGU_BASE_URL', 'https://api.paychangu.com/v1'),
    'api_key' => env('PAYCHANGU_API_KEY'),
    'merchant_id' => env('PAYCHANGU_MERCHANT_ID'),
    'webhook_secret' => env('PAYCHANGU_WEBHOOK_SECRET'),
  ],
];
