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
      $table->string('key')->unique();
      $table->text('value')->nullable();
      $table->string('type')->default('string'); // string, boolean, integer, json, etc.
      $table->string('group')->default('general'); // general, email, api, etc.
      $table->string('label');
      $table->text('description')->nullable();
      $table->json('options')->nullable(); // For select/radio/checkbox options
      $table->boolean('is_public')->default(false);
      $table->boolean('is_system')->default(false);
      $table->timestamps();
    });

    Schema::create('user_settings', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('key');
      $table->text('value')->nullable();
      $table->timestamps();

      $table->unique(['user_id', 'key']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_settings');
    Schema::dropIfExists('settings');
  }
};
