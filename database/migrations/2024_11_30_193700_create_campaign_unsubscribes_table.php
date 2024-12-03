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
    Schema::create('campaign_unsubscribes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
      $table->foreignId('recipient_id')->constrained()->cascadeOnDelete();
      $table->unique(['campaign_id', 'recipient_id']);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('campaign_unsubscribes');
  }
};
