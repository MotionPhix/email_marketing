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
    Schema::create('subscribers', function (Blueprint $table) {
      $table->id();
      $table->string('email')->unique();
      $table->string('first_name')->nullable();
      $table->string('last_name')->nullable();
      $table->string('company')->nullable();
      $table->string('status')->default('subscribed');
      $table->json('metadata')->nullable();
      $table->timestamp('unsubscribed_at')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('subscribers');
  }
};
