<?php

namespace App\Services;

use App\Exceptions\OnboardingException;
use App\Jobs\ImportContacts;
use App\Jobs\VerifyDomain;
use App\Models\OnboardingProgress;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Sendgrid\Mail\Mail;
use Throwable;

class OnboardingService
{
  // Define which steps are required
  private const REQUIRED_STEPS = [
    1, // Account setup
    2, // Contact import
  ];

  private const OPTIONAL_STEPS = [
    3, // Domain setup
    4, // Template creation
    5, // Test campaign
  ];

  public function getOrCreateProgress(User $user): OnboardingProgress
  {
    return $user->onboardingProgress ?? OnboardingProgress::create([
      'user_id' => $user->id,
      'completed_steps' => [],
      'skipped_steps' => [],
      'form_data' => [],
    ]);
  }

  public function updateStep(User $user, int $step, array $data): OnboardingProgress
  {
    $progress = $this->getOrCreateProgress($user);

    // Validate step sequence
    if (!$this->isStepSequenceValid($progress, $step)) {
      throw new OnboardingException(
        'Please complete previous required steps first',
        $step
      );
    }

    // Process step-specific data
    $this->processStepData($user, $step, $data);

    // Update progress
    $completedSteps = collect($progress->completed_steps);

    if (!$completedSteps->contains($step)) {
      $completedSteps->push($step);
    }

    // Remove from skipped if it was previously skipped
    $skippedSteps = collect($progress->skipped_steps)->reject(fn($s) => $s === $step);

    $formData = $progress->form_data;
    $formData["step_{$step}"] = $data;

    $progress->update([
      'completed_steps' => $completedSteps->toArray(),
      'skipped_steps' => $skippedSteps->toArray(),
      'form_data' => $formData,
    ]);

    return $progress;
  }

  public function skipStep(User $user, int $step): OnboardingProgress
  {
    if (in_array($step, self::REQUIRED_STEPS)) {
      throw new OnboardingException(
        'This step cannot be skipped',
        $step
      );
    }

    $progress = $this->getOrCreateProgress($user);

    // Can't skip if previous required steps aren't completed
    if (!$this->isStepSequenceValid($progress, $step)) {
      throw new OnboardingException(
        'Please complete previous required steps first',
        $step
      );
    }

    $skippedSteps = collect($progress->skipped_steps);

    if (!$skippedSteps->contains($step)) {
      $skippedSteps->push($step);
    }

    $progress->update([
      'skipped_steps' => $skippedSteps->toArray(),
    ]);

    return $progress;
  }

  private function isStepSequenceValid(OnboardingProgress $progress, int $currentStep): bool
  {
    if ($currentStep === 1) return true;

    $completedSteps = collect($progress->completed_steps);
    $skippedSteps = collect($progress->skipped_steps);

    // Get all previous required steps
    $previousRequiredSteps = collect(self::REQUIRED_STEPS)
      ->filter(fn($step) => $step < $currentStep);

    // return $completedSteps->contains($currentStep - 1);

    // All previous required steps must be completed
    return $previousRequiredSteps->every(fn($step) => $completedSteps->contains($step));
  }

  private function processStepData(User $user, int $step, array $data): void
  {
    try {
      match ($step) {
        2 => $this->processContactImport($user, $data),
        3 => $this->processDomainSetup($user, $data),
        4 => $this->processTemplateCreation($user, $data),
        5 => $this->processTestCampaign($user, $data),
        default => null
      };
    } catch (Throwable $e) {
      Log::error("Failed to process step $step", [
        'error' => $e->getMessage(),
        'user_id' => $user->id,
        'step' => $step,
      ]);

      throw new OnboardingException(
        "Failed to process step $step: " . $e->getMessage(),
        $step
      );
    }
  }

  private function processContactImport(User $user, array $data): void
  {
    // Validate each contact
    foreach ($data['contacts'] as $contact) {
      $validator = Validator::make($contact, [
        'email' => 'required|email',
        'first_name' => 'string|max:255',
        'last_name' => 'string|max:255',
      ]);

      if ($validator->fails()) {
        throw new OnboardingException(
          'Invalid contact data',
          2,
          $validator->errors()->toArray()
        );
      }
    }

    // Queue import job
    ImportContacts::dispatch($user, $data['contacts']);
  }

  private function processDomainSetup(User $user, array $data): void
  {
    // Verify domain exists
    if (!checkdnsrr($data['domain'], 'A')) {
      throw new OnboardingException(
        'Domain does not exist or is not properly configured',
        3
      );
    }

    // Queue domain verification job
    VerifyDomain::dispatch($user, $data['domain']);
  }

  private function processTemplateCreation(User $user, array $data): void
  {
    // Create template
    $template = $user->templates()->create([
      'name' => $data['template']['name'],
      'content' => $data['template']['content'],
    ]);

    if (!$template) {
      throw new OnboardingException(
        'Failed to create template',
        4
      );
    }
  }

  private function processTestCampaign(User $user, array $data): void
  {
    try {
      $email = new Mail();
      $email->setFrom($data['reply_to'], $data['from_name']);
      $email->setSubject($data['subject']);
      $email->addTo($data['test_email']);
      $email->addContent("text/html", "This is a test email from your email marketing platform");

      $sendgrid = new \SendGrid(config('services.sendgrid.key'));
      $response = $sendgrid->send($email);

      if ($response->statusCode() !== 202) {
        throw new OnboardingException(
          'Failed to send test email',
          5
        );
      }
    } catch (Throwable $e) {
      throw new OnboardingException(
        'Failed to send test email: ' . $e->getMessage(),
        5
      );
    }
  }

  public function completeOnboarding(User $user): void
  {
    $progress = $this->getOrCreateProgress($user);

    // Check if all required steps are completed
    $completedSteps = collect($progress->completed_steps);
    $skippedSteps = collect($progress->skipped_steps);

    $missingRequiredSteps = collect(self::REQUIRED_STEPS)
      ->reject(fn($step) => $completedSteps->contains($step));

    if ($missingRequiredSteps->isNotEmpty()) {
      throw new OnboardingException(
        'Please complete all required steps before finishing onboarding',
        $missingRequiredSteps->first()
      );
    }

    // All optional steps should either be completed or skipped
    $missingOptionalSteps = collect(self::OPTIONAL_STEPS)
      ->reject(fn($step) =>
        $completedSteps->contains($step) || $skippedSteps->contains($step)
      );

    if ($missingOptionalSteps->isNotEmpty()) {
      throw new OnboardingException(
        'Please complete or skip all remaining steps',
        $missingOptionalSteps->first()
      );
    }

    $progress->update([
      'is_completed' => true,
      'completed_at' => now(),
    ]);
  }
}
