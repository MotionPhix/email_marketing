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
      $table->uuid('uuid');
      $table->string('name');
      $table->integer('campaign_limit')->default(0);
      $table->integer('recipient_limit')->default(0);
      $table->integer('email_limit')->default(0);
      $table->integer('segment_limit')->default(0);
      $table->boolean('can_schedule_campaigns')->default(false);
      $table->timestamps();
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
