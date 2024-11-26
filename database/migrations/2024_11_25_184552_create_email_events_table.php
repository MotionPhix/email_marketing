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
      $table->unsignedBigInteger('campaign_id')->nullable();
      $table->string('recipient_email');
      $table->string('event_type'); // delivered, opened, clicked, etc.
      $table->timestamp('timestamp');
      $table->timestamps();
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
