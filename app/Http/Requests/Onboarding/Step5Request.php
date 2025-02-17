<?php

namespace App\Http\Requests\Onboarding;

class Step5Request extends BaseStepRequest
{
  public function stepRules(): array
  {
    return [
      'data.template' => ['required', 'array'],
      'data.template.name' => ['required', 'string', 'max:255'],
      'data.template.subject' => ['required', 'string', 'max:255'],
      'data.template.preheader' => ['nullable', 'string', 'max:255'],
      'data.template.content' => ['required', 'string', 'min:50'],
      'data.template.type' => ['required', 'string', 'in:html,markdown,drag-drop'],
      'data.template.category' => ['required', 'string', 'in:newsletter,promotional,transactional,announcement'],
      'data.template.tags' => ['nullable', 'array'],
      'data.template.tags.*' => ['string', 'max:50'],
    ];
  }

  public function messages(): array
  {
    return [
      'data.template.name.required' => 'Please provide a name for your template.',
      'data.template.subject.required' => 'Email subject line is required.',
      'data.template.content.required' => 'Template content is required.',
      'data.template.content.min' => 'Template content should be at least 50 characters.',
      'data.template.type.in' => 'Please select a valid template type.',
      'data.template.category.in' => 'Please select a valid template category.',
      'data.template.tags.*.max' => 'Each tag cannot exceed 50 characters.',
    ];
  }
}
