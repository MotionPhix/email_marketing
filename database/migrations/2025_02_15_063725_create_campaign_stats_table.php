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
    Schema::create('campaign_stats', function (Blueprint $table) {
      $table->id();
      $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
      $table->integer('recipients_count')->default(0);
      $table->integer('delivered_count')->default(0);
      $table->integer('opened_count')->default(0);
      $table->integer('clicked_count')->default(0);
      $table->integer('bounced_count')->default(0);
      $table->integer('complained_count')->default(0);
      $table->integer('unsubscribed_count')->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('campaign_stats');
  }
};
