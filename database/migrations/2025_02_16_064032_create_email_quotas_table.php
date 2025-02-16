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
    Schema::create('email_quotas', function (Blueprint $table) {
      $table->id();
      $table->integer('monthly_limit')->default(1000);
      $table->integer('monthly_used')->default(0);
      $table->integer('daily_limit')->default(100);
      $table->integer('daily_used')->default(0);
      $table->timestamp('last_reset_at');
      $table->timestamps();

      $table->foreignId('user_id')
        ->references('id')
        ->on('users')
        ->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('email_quotas');
  }
};
