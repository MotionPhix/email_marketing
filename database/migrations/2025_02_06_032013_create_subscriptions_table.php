<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('subscriptions', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->index();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
      $table->string('status');
      $table->string('paychangu_reference')->nullable();
      $table->timestamp('starts_at')->nullable();
      $table->timestamp('ends_at')->nullable();
      $table->timestamp('trial_ends_at')->nullable();
      $table->timestamp('cancelled_at')->nullable();
      $table->timestamp('last_payment_at')->nullable();
      $table->string('payment_method')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('subscriptions');
  }
};
