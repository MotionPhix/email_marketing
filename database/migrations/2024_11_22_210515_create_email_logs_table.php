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
    Schema::create('email_logs', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid');
      $table->string('message_id')->nullable();
      $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('cascade');
      $table->string('recipient_email');
      $table->text('useragent')->nullable();
      $table->string('category')->nullable();
      $table->string('status')->default('processed');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('email_logs');
  }
};
