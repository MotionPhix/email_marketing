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
    Schema::create('plans', function (Blueprint $table) {
      $table->id();

      $table->uuid();
      $table->string('name');
      $table->string('slug')->unique();
      $table->text('description')->nullable();
      $table->integer('price');
      $table->string('currency', 3)->default('MWK');
      $table->integer('trial_days')->default(14);
      $table->boolean('is_active')->default(true);
      $table->boolean('is_featured')->default(false);
      $table->integer('sort_order')->default(0);
      $table->json('features');
      $table->json('metadata')->nullable();

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('plans');
  }
};
