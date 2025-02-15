<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class EmailPersonalizationService
{
  protected $defaultVariables = [];
  protected $customVariables = [];

  public function __construct()
  {
    $this->initializeDefaultVariables();
  }

  protected function initializeDefaultVariables()
  {
    $this->defaultVariables = [
      'date' => Carbon::now()->format('F j, Y'),
      'time' => Carbon::now()->format('g:i A'),
      'year' => Carbon::now()->format('Y'),
      'unsubscribe_url' => '{{unsubscribe_url}}', // SendGrid will replace this
      'web_version_url' => '{{weblink}}', // SendGrid will replace this
    ];
  }

  public function setCustomVariables(array $variables)
  {
    $this->customVariables = $variables;
    return $this;
  }

  public function parseTemplate(string $content, array $recipientData = []): string
  {
    $variables = array_merge(
      $this->defaultVariables,
      $this->customVariables,
      $recipientData
    );

    return preg_replace_callback(
      '/\{\{([^}]+)\}\}/',
      function ($matches) use ($variables) {
        $key = trim($matches[1]);
        return $variables[$key] ?? $matches[0];
      },
      $content
    );
  }

  public function validateTemplate(string $content): array
  {
    $errors = [];
    preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);

    $usedVariables = array_unique($matches[1]);
    $availableVariables = array_keys(array_merge(
      $this->defaultVariables,
      $this->customVariables
    ));

    foreach ($usedVariables as $variable) {
      $variable = trim($variable);
      if (!in_array($variable, $availableVariables)) {
        $errors[] = "Unknown variable: {$variable}";
      }
    }

    return $errors;
  }
}
