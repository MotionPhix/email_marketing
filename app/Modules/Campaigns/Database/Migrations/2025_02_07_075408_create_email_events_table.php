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
    Schema::create('email_events', function (Blueprint $table) {
      $table->id();
      $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
      $table->foreignId('recipient_id')->constrained()->cascadeOnDelete();
      $table->string('event_type');
      $table->json('metadata')->nullable();
      $table->string('ip_address')->nullable();
      $table->string('user_agent')->nullable();
      $table->timestamps();

      $table->index(['campaign_id', 'event_type']);
      $table->index(['recipient_id', 'event_type']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('email_events');
  }
};
