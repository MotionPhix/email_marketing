<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

\Illuminate\Support\Facades\Schedule::command('subscriptions:process-renewals')
  ->daily()
  ->at('00:00')
  ->runInBackground();

\Illuminate\Support\Facades\Schedule::command('subscriptions:process-scheduled')
  ->hourly();
