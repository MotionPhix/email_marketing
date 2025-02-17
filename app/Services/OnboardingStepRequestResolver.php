<?php

namespace App\Services;

use App\Http\Requests\Onboarding\BaseStepRequest;
use App\Http\Requests\Onboarding\Step2Request;
use App\Http\Requests\Onboarding\Step3Request;
use App\Http\Requests\Onboarding\Step4Request;
use App\Http\Requests\Onboarding\Step5Request;
use App\Http\Requests\Onboarding\Step6Request;
use Illuminate\Http\Request;

class OnboardingStepRequestResolver
{
  private const STEP_REQUESTS = [
    2 => Step2Request::class,
    3 => Step3Request::class,
    4 => Step4Request::class,
    5 => Step5Request::class,
    6 => Step6Request::class,
  ];

  public function resolve(Request $request): BaseStepRequest
  {
    $step = $request->get('step');
    $requestClass = self::STEP_REQUESTS[$step] ?? null;

    if (!$requestClass) {
      throw new \InvalidArgumentException("Invalid step number: {$step}");
    }

    return new $requestClass();
  }
}
