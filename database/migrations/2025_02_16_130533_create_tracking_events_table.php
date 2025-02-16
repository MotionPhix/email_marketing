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
    Schema::create('tracking_events', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('cascade');
      $table->string('type'); // sent, opened, clicked, bounced, complained, etc.
      $table->string('email')->nullable();
      $table->json('metadata')->nullable();
      $table->timestamp('occurred_at');
      $table->timestamps();

      // Index for quick lookups
      $table->index(['user_id', 'type', 'created_at']);
      $table->index(['campaign_id', 'type', 'created_at']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tracking_events');
  }
};
