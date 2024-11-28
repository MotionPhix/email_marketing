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
    Schema::create('audience_recipient', function (Blueprint $table) {
      $table->id();
      $table->foreignId('audience_id')->constrained()->cascadeOnDelete();
      $table->foreignId('recipient_id')->constrained()->cascadeOnDelete();
      $table->unique(['audience_id', 'recipient_id']);
      $table->index('audience_id');
      $table->index('recipient_id');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('audience_recipient');
  }
};
