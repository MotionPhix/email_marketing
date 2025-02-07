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
    Schema::create('campaign_queues', function (Blueprint $table) {
      $table->id();
      $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
      $table->foreignId('recipient_id')->constrained()->cascadeOnDelete();
      $table->string('status')->default('pending');
      $table->text('error_message')->nullable();
      $table->timestamp('scheduled_at')->nullable();
      $table->timestamp('sent_at')->nullable();
      $table->timestamps();

      $table->index(['campaign_id', 'status']);
      $table->index(['status', 'scheduled_at']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('campaign_queues');
  }
};
