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
    Schema::create('email_logs', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid');
      $table->string('sg_message_id')->unique();
      $table->string('campaign_uuid')->nullable();
      $table->string('email');
      $table->string('user_uuid')->nullable();
      $table->timestamps();

      $table->index(['campaign_id', 'status']);
      $table->index('created_at');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('email_logs');
  }
};
