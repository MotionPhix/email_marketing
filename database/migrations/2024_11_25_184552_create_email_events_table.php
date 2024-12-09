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
    Schema::create('email_events', function (Blueprint $table) {
      $table->id();
      $table->foreignId('email_log_id')->constrained('email_logs')->onDelete('cascade');
      $table->string('event'); // 'processed', 'delivered', 'open', 'click', etc.
      $table->string('ip')->nullable();
      $table->string('user_agent')->nullable();
      $table->string('url')->nullable();
      $table->text('reason')->nullable();
      $table->string('status')->nullable();
      $table->timestamp('timestamp')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('email_events');
  }
};
