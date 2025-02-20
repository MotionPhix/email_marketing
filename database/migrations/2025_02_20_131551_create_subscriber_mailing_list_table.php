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
    Schema::create('subscriber_mailing_list', function (Blueprint $table) {
      $table->id();
      $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
      $table->foreignId('mailing_list_id')->constrained('mailing_lists')->cascadeOnDelete();
      $table->string('status')->default('subscribed'); // subscribed, unsubscribed, bounced, complained
      $table->timestamp('subscribed_at')->nullable();
      $table->timestamp('unsubscribed_at')->nullable();
      $table->timestamps();

      // Prevent duplicate subscriptions
      $table->unique(['subscriber_id', 'mailing_list_id']);

      // Add indexes for better query performance
      $table->index(['mailing_list_id', 'status']);
      $table->index(['subscriber_id', 'status']);
      $table->index('status');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('subscriber_mailing_list');
  }
};
