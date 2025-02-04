<?php

namespace Tests\Unit\Services\Campaign;

use App\Models\Campaign;
use App\Models\User;
use App\Services\Campaign\CampaignSchedulingService;
use App\Jobs\ScheduleCampaignJob;
use App\Events\CampaignScheduled;
use App\Events\CampaignScheduleCancelled;
use Carbon\Carbon;
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

  protected function setUp(): void
  {
    parent::setUp();

    $this->service = new CampaignSchedulingService();

    $this->user = User::factory()->create([
      'uuid' => Str::uuid(),
      'first_name' => 'John',
      'last_name' => 'Doe',
      'email' => 'test@example.com',
    ]);

    $this->campaign = Campaign::factory()->create([
      'user_id' => $this->user->id,
      'uuid' => Str::uuid(),
      'status' => Campaign::STATUS_DRAFT,
      'scheduled_at' => null,
      'end_date' => null,
      'title' => 'Test Campaign',
      'subject' => 'Test Subject'
    ]);

    Queue::fake();
    Event::fake();
    Cache::flush();
  }

  /** @test */
  public function it_schedules_one_time_campaign()
  {
    $startDate = Carbon::tomorrow();

    $this->service->schedule($this->campaign, $startDate, 'once');

    $freshCampaign = Campaign::where('uuid', $this->campaign->uuid)->first();

    Queue::assertPushed(ScheduleCampaignJob::class, function ($job) {
      return $job->campaign->is($this->campaign);
    });

    $this->assertEquals(Campaign::STATUS_SCHEDULED, $freshCampaign->status);
    $this->assertNotNull($freshCampaign->scheduled_at);
    $this->assertNull($freshCampaign->end_date);
    $this->assertTrue(
      $startDate->startOfDay()->equalTo($freshCampaign->scheduled_at->startOfDay()),
      "Expected {$startDate} to equal {$freshCampaign->scheduled_at}"
    );

    Event::assertDispatched(CampaignScheduled::class);
  }

  /** @test */
  public function it_schedules_daily_campaign()
  {
    $startDate = Carbon::tomorrow()->startOfDay();
    $endDate = $startDate->copy()->addDays(5)->endOfDay();

    $this->service->schedule($this->campaign, $startDate, 'daily', $endDate);

    $freshCampaign = $this->campaign->fresh();

    // For daily campaigns, we expect jobs to be pushed
    Queue::assertPushed(ScheduleCampaignJob::class, function ($job) {
      return $job->campaign->is($this->campaign);
    });

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
  public function it_cancels_scheduled_campaign()
  {
    // First schedule the campaign
    $startDate = Carbon::tomorrow();
    $this->service->schedule($this->campaign, $startDate, 'once');

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
//  public function it_schedules_one_time_campaign()
//  {
//    $startDate = Carbon::tomorrow();
//
//    $this->service->schedule($this->campaign, $startDate, 'once');
//
//    $freshCampaign = Campaign::where('uuid', $this->campaign->uuid)->first();
//
//    Queue::assertPushed(ScheduleCampaignJob::class, function ($job) {
//      return $job->campaign->is($this->campaign);
//    });
//
//    $this->assertEquals(Campaign::STATUS_SCHEDULED, $freshCampaign->status);
//    $this->assertNotNull($freshCampaign->scheduled_at);
//    // One-time campaigns don't have an end date
//    $this->assertNull($freshCampaign->end_date);
//    $this->assertTrue(
//      $startDate->equalTo($freshCampaign->scheduled_at),
//      "Expected {$startDate} to equal {$freshCampaign->scheduled_at}"
//    );
//
//    Event::assertDispatched(CampaignScheduled::class);
//  }

  /** @test */
  public function it_prevents_scheduling_in_the_past()
  {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Start date cannot be in the past');

    $this->service->schedule(
      $this->campaign,
      Carbon::yesterday(),
      'once'
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

    $this->service->schedule($this->campaign, $startDate, 'once');
  }

  /** @test */
  public function it_caches_schedule_information()
  {
    $startDate = Carbon::tomorrow();

    $this->service->schedule($this->campaign, $startDate, 'once');

    $cacheKey = 'campaign_schedule:' . $this->campaign->uuid;
    $cachedInfo = Cache::get($cacheKey);

    $this->assertNotNull($cachedInfo);
    $this->assertEquals($startDate->toDateTimeString(), $cachedInfo['start_date']);
    $this->assertEquals('once', $cachedInfo['frequency']);
  }
}
