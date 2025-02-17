<?php

namespace App\Http\Controllers;

use App\Exceptions\OnboardingException;
use App\Http\Requests\Onboarding\UpdateStepRequest;
use App\Services\OnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class OnboardingController extends Controller
{
  public function __construct(
    protected OnboardingService $onboardingService
  ) {}

  public function index(): Response
  {
    try {
      $progress = $this->onboardingService->getOrCreateProgress(auth()->user());

      return Inertia::render('OnBoarding/Index', [
        'progress' =>  [
          'current_step' => $progress->current_step,
          'completed_steps' => $progress->completed_steps ?? [],
          'skipped_steps' => $progress->skipped_steps ?? [],
          'form_data' => $progress->form_data ?? [],
          'is_completed' => $progress->is_completed,
        ],
        'required_steps' => OnboardingService::REQUIRED_STEPS,
        'userSettings' => auth()->user()->settings,
      ]);
    } catch (Throwable $e) {
      Log::error('Failed to load onboarding page', [
        'error' => $e->getMessage(),
        'user_id' => auth()->id(),
      ]);

      return Inertia::render('Error', [
        'status' => 500,
        'message' => 'Failed to load onboarding. Please try again.',
      ]);
    }
  }

  public function skip(int $step)
  {
    try {
      DB::beginTransaction();

      $progress = $this->onboardingService->skipStep(
        auth()->user(),
        $step
      );

      DB::commit();

      return back()->with([
        'message' => 'Step skipped successfully',
        'progress' => $progress->fresh(),
      ]);
    } catch (OnboardingException $e) {
      DB::rollBack();

      return back()->withErrors(['step' => $e->getMessage()], 'default');
    } catch (Throwable $e) {
      DB::rollBack();
      Log::error('Failed to skip onboarding step', [
        'error' => $e->getMessage(),
        'step' => $step,
        'user_id' => auth()->id(),
      ]);

      return response()->json([
        'message' => 'Failed to skip step. Please try again.',
      ], 500);
    }
  }

  public function updateStep(UpdateStepRequest $request)
  {
    dd($request->all());

    try {
      DB::beginTransaction();

      $progress = $this->onboardingService->updateStep(
        $request->user(),
        $request->step,
        $request->validated('data')
      );

      DB::commit();

      return back()->with([
        'message' => 'Step updated successfully',
        'progress' => $progress->fresh(),
      ]);
    } catch (OnboardingException $e) {
      DB::rollBack();

      return back()->withErrors([
        'step' => $e->getMessage(),
        'errors' => $e->getErrors(),
      ]);
    } catch (Throwable $e) {
      DB::rollBack();
      Log::error('Failed to update onboarding step', [
        'error' => $e->getMessage(),
        'step' => $request->step,
        'user_id' => auth()->id(),
      ]);

      return back()->withErrors([
        'message' => 'Failed to update step. Please try again.',
      ]);
    }
  }

  public function completeOnboarding(): JsonResponse
  {
    try {
      DB::beginTransaction();

      $this->onboardingService->completeOnboarding(auth()->user());

      DB::commit();

      return response()->json([
        'message' => 'Onboarding completed successfully',
      ]);
    } catch (OnboardingException $e) {
      DB::rollBack();

      return response()->json([
        'message' => $e->getMessage(),
        'errors' => $e->getErrors(),
      ], 422);
    } catch (Throwable $e) {
      DB::rollBack();
      Log::error('Failed to complete onboarding', [
        'error' => $e->getMessage(),
        'user_id' => auth()->id(),
      ]);

      return response()->json([
        'message' => 'Failed to complete onboarding. Please try again.',
      ], 500);
    }
  }
}
