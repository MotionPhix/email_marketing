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
      $table->string('message_id')->nullable();
      $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('cascade');
      $table->string('recipient_email');
      $table->enum('status', [
        'processed',
        'group_unsubscribe',
        'group_resubscribe',
        'unsubscribe',
        'delivered',
        'spamreport',
        'deferred',
        'click',
        'dropped',
        'open',
        'bounce'])->default('processed');
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
