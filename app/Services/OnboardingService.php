<?php

namespace App\Services;

use App\Exceptions\OnboardingException;
use App\Models\OnboardingProgress;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Sendgrid\Mail\Mail;
use Throwable;

class OnboardingService
{
  public function getOrCreateProgress(User $user): OnboardingProgress
  {
    return $user->onboardingProgress ?? OnboardingProgress::create([
      'user_id' => $user->id,
      'completed_steps' => [],
      'form_data' => [],
    ]);
  }

  public function updateStep(User $user, int $step, array $data): OnboardingProgress
  {
    $progress = $this->getOrCreateProgress($user);

    // Validate step sequence
    if (!$this->isStepSequenceValid($progress, $step)) {
      throw new OnboardingException(
        'Please complete previous steps first',
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

    $formData = $progress->form_data;
    $formData["step_{$step}"] = $data;

    $progress->update([
      'completed_steps' => $completedSteps->toArray(),
      'form_data' => $formData,
    ]);

    return $progress;
  }

  private function isStepSequenceValid(OnboardingProgress $progress, int $currentStep): bool
  {
    if ($currentStep === 1) return true;

    $completedSteps = collect($progress->completed_steps);
    return $completedSteps->contains($currentStep - 1);
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

    // Verify all steps are completed
    if (count($progress->completed_steps) !== 5) {
      throw new OnboardingException(
        'Please complete all steps before finishing onboarding'
      );
    }

    $progress->update([
      'is_completed' => true,
      'completed_at' => now(),
    ]);
  }
}
