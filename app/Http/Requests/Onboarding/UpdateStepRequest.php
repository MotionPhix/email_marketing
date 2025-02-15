<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStepRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'step' => ['required', 'integer', 'between:1,5'],
      'data' => ['required', 'array'],
      'data.*' => ['sometimes'],

      // Step 2: Contact Import Validation
      'data.contacts' => [
        Rule::requiredIf(fn() => $this->step === 2),
        'array',
      ],
      'data.contacts.*.email' => [
        Rule::requiredIf(fn() => $this->step === 2),
        'email',
      ],
      'data.contacts.*.first_name' => ['sometimes', 'string', 'max:255'],
      'data.contacts.*.last_name' => ['sometimes', 'string', 'max:255'],

      // Step 3: Domain Setup Validation
      'data.domain' => [
        Rule::requiredIf(fn() => $this->step === 3),
        'string',
        'regex:/^(?!:\/\/)(?=.{1,255}$)((.{1,63}\.){1,127}(?![0-9]*$)[a-z0-9-]+\.?)$/i',
      ],

      // Step 4: Template Validation
      'data.template' => [
        Rule::requiredIf(fn() => $this->step === 4),
        'array',
      ],
      'data.template.name' => [
        Rule::requiredIf(fn() => $this->step === 4),
        'string',
        'max:255',
      ],
      'data.template.content' => [
        Rule::requiredIf(fn() => $this->step === 4),
        'string',
      ],

      // Step 5: Test Campaign Validation
      'data.from_name' => [
        Rule::requiredIf(fn() => $this->step === 5),
        'string',
        'max:255',
      ],
      'data.reply_to' => [
        Rule::requiredIf(fn() => $this->step === 5),
        'email',
      ],
      'data.subject' => [
        Rule::requiredIf(fn() => $this->step === 5),
        'string',
        'max:255',
      ],
      'data.test_email' => [
        Rule::requiredIf(fn() => $this->step === 5),
        'email',
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'data.domain.regex' => 'Please enter a valid domain name.',
      'data.contacts.*.email.email' => 'Each contact must have a valid email address.',
      'data.template.content.required' => 'Template content is required.',
      'data.from_name.required' => 'Sender name is required.',
      'data.reply_to.email' => 'Please enter a valid reply-to email address.',
    ];
  }
}
