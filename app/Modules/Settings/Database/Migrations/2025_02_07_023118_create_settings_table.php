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
    Schema::create('email_providers', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('slug')->unique();
      $table->text('description')->nullable();
      $table->json('required_fields');
      $table->boolean('is_enabled')->default(true);
      $table->timestamps();
    });

    Schema::create('settings', function (Blueprint $table) {
      $table->id();
      $table->string('key')->unique();
      $table->text('value')->nullable();
      $table->string('type')->default('string');
      $table->string('group')->default('general');
      $table->string('label');
      $table->text('description')->nullable();
      $table->json('options')->nullable();
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

    Schema::create('user_email_providers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('email_provider_id')->constrained('email_providers')->cascadeOnDelete();
      $table->json('credentials');
      $table->boolean('is_active')->default(false);
      $table->timestamp('last_used_at')->nullable();
      $table->timestamps();

      $table->unique(['user_id', 'email_provider_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_email_providers');
    Schema::dropIfExists('email_providers');
    Schema::dropIfExists('user_settings');
    Schema::dropIfExists('settings');
  }
};
