<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseStepRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  abstract public function stepRules(): array;

  public function rules(): array
  {
    return [
      'step' => ['required', 'integer', 'between:1,6'],
      'data' => array_merge(
        ['required', 'array'],
        $this->stepRules()
      ),
    ];
  }
}
