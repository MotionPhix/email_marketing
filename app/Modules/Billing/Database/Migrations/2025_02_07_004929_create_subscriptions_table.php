<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('subscriptions', function (Blueprint $table) {
      $table->id();
      $table->uuid();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('plan_id')->constrained();
      $table->string('status');
      $table->timestamp('trial_ends_at')->nullable();
      $table->timestamp('starts_at');
      $table->timestamp('ends_at')->nullable();
      $table->timestamp('cancelled_at')->nullable();
      $table->timestamp('last_payment_at')->nullable();
      $table->string('paychangu_transaction_id')->nullable()->index();
      $table->string('paychangu_payment_status')->nullable();
      $table->json('payment_metadata')->nullable();
      $table->timestamps();

      $table->index(['user_id', 'status']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('subscriptions');
  }
};
