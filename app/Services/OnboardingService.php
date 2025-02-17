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
  public const REQUIRED_STEPS = [
    2, // Account setup
    3, // Contact import
  ];

  private const OPTIONAL_STEPS = [
    1, // Welcome
    4, // Domain setup
    5, // Template creation
    6, // Test campaign
  ];

  public function getOrCreateProgress(User $user): OnboardingProgress
  {
    return $user->onboardingProgress ?? OnboardingProgress::create([
      'user_id' => $user->id,
      'completed_steps' => [],
      'skipped_steps' => [],
      'current_step' => 1,
      'form_data' => [],
    ]);
  }

  public function updateStep(User $user, int $step, array $data): OnboardingProgress
  {
    $progress = $this->getOrCreateProgress($user);

    // Don't allow repeating completed steps
    if (in_array($step, $progress->completed_steps ?? [])) {
      throw new OnboardingException(
        'This step has already been completed',
        $step
      );
    }

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

    // Calculate next step
    $nextStep = $this->calculateNextStep($completedSteps->toArray(), $progress->skipped_steps ?? []);

    $progress->update([
      'completed_steps' => $completedSteps->toArray(),
      'skipped_steps' => $skippedSteps->toArray(),
      'current_step' => $nextStep,
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

    // Calculate next step
    $nextStep = $this->calculateNextStep($progress->completed_steps ?? [], $skippedSteps->toArray());

    $progress->update([
      'skipped_steps' => $skippedSteps->unique()->toArray(),
      'current_step' => $nextStep,
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

    // All previous required steps must be completed
    return $previousRequiredSteps->every(fn($s) => $completedSteps->contains($s) || $skippedSteps->contains($s)
    );
  }

  private function processStepData(User $user, int $step, array $data): void
  {
    try {
      match ($step) {
        2 => $this->processEmailDefaultSettings($user, $data),
        3 => $this->processContactImport($user, $data),
        4 => $this->processDomainSetup($user, $data),
        5 => $this->processTemplateCreation($user, $data),
        6 => $this->processTestCampaign($user, $data),
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

  private function processEmailDefaultSettings(User $user, array $data): void
  {
    $user->settings()->update([
      'sender_settings' => array_merge(
        $user->settings->sender_settings ?? [],
        $data['sender_settings'] ?? []
      ),
      'email_settings' => array_merge(
        $user->settings->email_settings ?? [],
        $data['email_settings'] ?? []
      ),
      'preferences' => array_merge(
        $user->settings->preferences ?? [],
        $data['preferences'] ?? []
      ),
    ]);
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

  private function calculateNextStep(array $completedSteps, array $skippedSteps): int
  {
    $allSteps = range(1, 5);
    $remainingSteps = array_diff($allSteps, $completedSteps, $skippedSteps);

    return empty($remainingSteps) ? max($completedSteps) : min($remainingSteps);
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

    // $missingRequiredSteps = collect(self::REQUIRED_STEPS)
    //  ->reject(fn($step) => $completedSteps->contains($step));

    $missingRequiredSteps = collect(self::REQUIRED_STEPS)
      ->diff($completedSteps);

    if ($missingRequiredSteps->isNotEmpty()) {
      throw new OnboardingException(
        'Please complete all required steps before finishing onboarding',
        $missingRequiredSteps->first()
      );
    }

    // All optional steps should either be completed or skipped
    $missingOptionalSteps = collect(self::OPTIONAL_STEPS)
      ->reject(fn($step) => $completedSteps->contains($step) || $skippedSteps->contains($step)
      );

    /*$missingOptionalSteps = collect(self::OPTIONAL_STEPS)
      ->diff($completedSteps)
      ->diff($skippedSteps);*/

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
