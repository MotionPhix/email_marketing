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
      $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
      $table->string('recipient_email');
      $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
      $table->timestamps();
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
