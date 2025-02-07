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
    Schema::create('campaign_recipients', function (Blueprint $table) {
      $table->id();
      $table->uuid('campaign_id')->index();
      $table->uuid('recipient_id');
      $table->timestamps();

      $table->foreign('campaign_id')
        ->references('id')
        ->on('campaigns')
        ->cascadeOnDelete();

      $table->foreign('recipient_id')
        ->references('id')
        ->on('recipients')
        ->cascadeOnDelete();

      $table->unique(['campaign_id', 'recipient_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('campaign_recipients');
  }
};
