<?php

namespace App\Http\Requests\Onboarding;

class Step3Request extends BaseStepRequest
{
  public function stepRules(): array
  {
    return [
      'data.contacts' => ['required', 'array'],
      'data.contacts.*.email' => ['required', 'email'],
      'data.contacts.*.first_name' => ['sometimes', 'string', 'max:255'],
      'data.contacts.*.last_name' => ['sometimes', 'string', 'max:255'],
    ];
  }

  public function messages(): array
  {
    return [
      'data.contacts.required' => 'Please provide at least one contact.',
      'data.contacts.*.email.required' => 'Each contact must have an email address.',
      'data.contacts.*.email.email' => 'Please provide valid email addresses.',
    ];
  }
}
