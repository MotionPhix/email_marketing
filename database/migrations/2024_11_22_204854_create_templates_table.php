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
    Schema::create('templates', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid');
      $table->string('name');
      $table->enum('mode', ['static', 'dynamic'])->default('static');
      $table->text('description')->nullable();
      $table->longText('content')->nullable();
      $table->json('design')->nullable();
      $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
      $table->enum('type', ['user', 'free', 'premium'])->default('user');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('templates');
  }
};
