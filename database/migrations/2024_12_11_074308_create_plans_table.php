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
      $table->uuid('uuid')->index();
      $table->string('name');
      $table->string('slug')->unique(); // For URL-friendly names
      $table->text('description')->nullable(); // Plan description
      $table->integer('price');
      $table->string('currency')->default('MWK');
      $table->integer('trial_days')->default(14);
      $table->boolean('is_active')->default(true);
      $table->boolean('is_featured')->default(false); // For highlighting special plans
      $table->integer('sort_order')->default(0); // For custom ordering
      $table->json('features');
      $table->json('metadata')->nullable(); // For any additional data
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
