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
    Schema::create('campaign_events', function (Blueprint $table) {
      $table->id();
      $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
      $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
      $table->string('type');
      $table->json('metadata')->nullable();
      $table->timestamps();

      $table->index(['campaign_id', 'type']);
      $table->index(['subscriber_id', 'type']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('campaign_events');
  }
};
