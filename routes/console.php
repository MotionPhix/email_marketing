<?php

\Illuminate\Support\Facades\Schedule::command('subscriptions:process-renewals')
  ->daily()
  ->at('00:00')
  ->runInBackground();

\Illuminate\Support\Facades\Schedule::command('subscriptions:process-scheduled')
  ->hourly();
