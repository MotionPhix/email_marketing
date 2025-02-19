<?php

namespace Tests\Unit\Services\Campaign;

use App\Models\Campaign;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use App\Services\Campaign\CampaignSchedulingService;
use App\Jobs\ScheduleCampaignJob;
use App\Events\CampaignScheduled;
use App\Events\CampaignScheduleCancelled;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CampaignSchedulingServiceTest extends TestCase
{
  use RefreshDatabase;

  private CampaignSchedulingService $service;
  private Campaign $campaign;
  private User $user;
  private Plan $plan;
  private Subscription $subscription;

  protected function setUp(): void
  {
    parent::setUp();

    DB::listen(function($query) {
      logger($query->sql, $query->bindings);
    });

    $this->service = new CampaignSchedulingService();

    // Create a paid plan
    // Create a paid plan
    $this->plan = Plan::factory()->create([
      'name' => 'Pro Plan',
      'price' => 999,
      'features' => [
        'can_schedule_campaigns' => true,
        'campaign_limit' => 10,
        'email_limit' => '50,000',
        'recipient_limit' => '10,000',
        'segment_limit' => 5,
        'personalisation' => true,
        'analytics' => 'Advanced analytics',
        'support_type' => 'Priority support'
      ]
    ]);

    // Create user with subscription
    $this->user = User::factory()->create([
      'uuid' => Str::uuid(),
      'first_name' => 'John',
      'last_name' => 'Doe',
      'email' => 'test@example.com',
    ]);

    // Create active subscription
    $this->subscription = Subscription::factory()->create([
      'user_id' => $this->user->id,
      'plan_id' => $this->plan->id,
      'status' => 'active',
      'starts_at' => now(),
      'ends_at' => null
    ]);

    $this->campaign = Campaign::factory()->create([
      'user_id' => $this->user->id,
      'uuid' => Str::uuid(),
      'status' => Campaign::STATUS_DRAFT,
      'scheduled_at' => null,
      'end_date' => null,
      'title' => 'Test Index',
      'subject' => 'Test Subject'
    ]);

    Queue::fake();
    Event::fake();
    Cache::flush();
  }

  /** @test */
  public function it_schedules_daily_campaign_with_default_end_date()
  {
    $startDate = Carbon::tomorrow()->startOfDay();

    $this->service->schedule($this->campaign, $startDate, 'daily');

    Queue::assertPushed(ScheduleCampaignJob::class, function ($job) use ($startDate) {
      return $job->campaign->is($this->campaign) &&
        $job->userId === $this->user->id &&
        $job->frequency === 'daily' &&
        $job->scheduledDate->equalTo($startDate);
    });

    $freshCampaign = $this->campaign->fresh();

    Queue::assertPushed(ScheduleCampaignJob::class);

    $this->assertEquals(Campaign::STATUS_SCHEDULED, $freshCampaign->status);
    $this->assertNotNull($freshCampaign->scheduled_at);
    $this->assertNotNull($freshCampaign->end_date);

    // Verify default end date is 30 days from start
    $this->assertTrue(
      $startDate->copy()->addDays(30)->startOfDay()->equalTo($freshCampaign->end_date->startOfDay()),
      "Expected end date to be 30 days after start date"
    );

    Event::assertDispatched(CampaignScheduled::class);
  }

  /** @test */
  public function it_schedules_daily_campaign_with_custom_end_date()
  {
    $startDate = Carbon::tomorrow()->startOfDay();
    $endDate = $startDate->copy()->addDays(5)->endOfDay();

    $this->service->schedule($this->campaign, $startDate, 'daily', $endDate);

    $freshCampaign = $this->campaign->fresh();

    Queue::assertPushed(ScheduleCampaignJob::class);

    $this->assertEquals(Campaign::STATUS_SCHEDULED, $freshCampaign->status);
    $this->assertNotNull($freshCampaign->scheduled_at);
    $this->assertNotNull($freshCampaign->end_date);
    $this->assertTrue(
      $startDate->startOfDay()->equalTo($freshCampaign->scheduled_at->startOfDay()),
      "Expected {$startDate} to equal {$freshCampaign->scheduled_at}"
    );
    $this->assertTrue(
      $endDate->startOfDay()->equalTo($freshCampaign->end_date->startOfDay()),
      "Expected {$endDate} to equal {$freshCampaign->end_date}"
    );
  }

  /** @test */
  public function it_prevents_scheduling_for_unpaid_users()
  {
    // Delete the subscription to simulate unpaid user
    $this->subscription->delete();

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Index scheduling is only available for paid plans.');

    $this->service->schedule(
      $this->campaign,
      Carbon::tomorrow(),
      'daily'
    );
  }

  /** @test */
  public function it_cancels_scheduled_campaign()
  {
    // First schedule the campaign
    $startDate = Carbon::tomorrow();
    $this->service->schedule($this->campaign, $startDate, 'daily');

    // Verify campaign was scheduled
    $this->assertEquals(Campaign::STATUS_SCHEDULED, $this->campaign->fresh()->status);

    // Then cancel it
    $this->service->cancelScheduledJobs($this->campaign);

    // Get fresh instance after cancellation
    $freshCampaign = $this->campaign->fresh();

    $this->assertEquals(Campaign::STATUS_DRAFT, $freshCampaign->status);
    $this->assertNull($freshCampaign->scheduled_at);
    $this->assertNull($freshCampaign->end_date);

    Event::assertDispatched(CampaignScheduleCancelled::class);
  }

  /** @test */
  public function it_prevents_scheduling_in_the_past()
  {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Start date cannot be in the past');

    $this->service->schedule(
      $this->campaign,
      Carbon::yesterday(),
      'daily'
    );
  }

  /** @test */
  public function it_prevents_invalid_frequency()
  {
    $this->expectException(\InvalidArgumentException::class);

    $this->service->schedule(
      $this->campaign,
      Carbon::tomorrow(),
      'invalid_frequency'
    );
  }

  /** @test */
  public function it_enforces_concurrent_campaign_limit()
  {
    $startDate = Carbon::tomorrow();

    // Create 5 campaigns scheduled for tomorrow
    for ($i = 0; $i < 5; $i++) {
      Campaign::factory()->create([
        'user_id' => $this->user->id,
        'scheduled_at' => $startDate,
        'status' => Campaign::STATUS_SCHEDULED
      ]);
    }

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Maximum concurrent campaigns limit reached for this date');

    $this->service->schedule($this->campaign, $startDate, 'daily');
  }

  /** @test */
  public function it_caches_schedule_information()
  {
    $startDate = Carbon::tomorrow();

    $this->service->schedule($this->campaign, $startDate, 'daily');

    $cacheKey = 'campaign_schedule:' . $this->campaign->uuid;
    $cachedInfo = Cache::get($cacheKey);

    $this->assertNotNull($cachedInfo);
    $this->assertEquals($startDate->toDateTimeString(), $cachedInfo['start_date']);
    $this->assertEquals('daily', $cachedInfo['frequency']);
  }
}
