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
    Schema::create('settings', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid');
      $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
      $table->foreignId('plan_id')->index()->nullable()->constrained()->cascadeOnDelete();
      $table->string('email_from_address')->nullable();
      $table->string('email_from_name')->nullable();
      $table->string('sender_name')->nullable();
      $table->string('timezone')->default('UTC');
      $table->json('email_settings')->nullable(); // For additional email settings
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('settings');
  }
};
