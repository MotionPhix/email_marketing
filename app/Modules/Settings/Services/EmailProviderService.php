<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\EmailProvider;
use App\Modules\Settings\Models\UserEmailProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EmailProviderService
{
  public function validateProvider(
    EmailProvider $provider,
    array $credentials
  ): bool {
    $validator = Validator::make($credentials, $this->getValidationRules($provider));

    if ($validator->fails()) {
      throw ValidationException::withMessages($validator->errors()->toArray());
    }

    return $this->testConnection($provider, $credentials);
  }

  public function setDefaultProvider(
    int $userId,
    EmailProvider $provider,
    array $credentials
  ): void {
    // Deactivate all other providers
    UserEmailProvider::where('user_id', $userId)
      ->where('email_provider_id', '!=', $provider->id)
      ->update(['is_active' => false]);

    // Create or update the selected provider
    UserEmailProvider::updateOrCreate(
      [
        'user_id' => $userId,
        'email_provider_id' => $provider->id,
      ],
      [
        'credentials' => $credentials,
        'is_active' => true,
        'last_used_at' => now(),
      ]
    );
  }

  private function getValidationRules(EmailProvider $provider): array
  {
    $rules = [];

    foreach ($provider->required_fields as $field => $config) {
      $fieldRules = ['required'];

      switch ($config['type']) {
        case 'email':
          $fieldRules[] = 'email';
          break;
        case 'url':
          $fieldRules[] = 'url';
          break;
        case 'password':
          $fieldRules[] = 'string';
          $fieldRules[] = 'min:8';
          break;
      }

      $rules[$field] = $fieldRules;
    }

    return $rules;
  }

  private function testConnection(EmailProvider $provider, array $credentials): bool
  {
    try {
      // Implementation for each provider
      return match($provider->slug) {
        'sendgrid' => $this->testSendGrid($credentials),
        'mailtrap' => $this->testMailtrap($credentials),
        default => throw new \Exception('Unsupported email provider'),
      };
    } catch (\Exception $e) {
      throw ValidationException::withMessages([
        'connection' => ['Failed to connect: ' . $e->getMessage()]
      ]);
    }
  }

  private function testSendGrid(array $credentials): bool
  {
    $client = new \SendGrid($credentials['api_key']);
    $response = $client->client->mail()->send()->post([
      'personalizations' => [
        ['to' => [['email' => 'test@example.com']]]
      ],
      'from' => ['email' => 'test@example.com'],
      'subject' => 'Test Connection',
      'content' => [
        ['type' => 'text/plain', 'value' => 'Test connection']
      ]
    ]);

    return $response->statusCode() === 202;
  }

  private function testMailtrap(array $credentials): bool
  {
    // Implement Mailtrap connection test
    return true;
  }
}
