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
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->json('preferences')->nullable();
      $table->json('notification_settings')->nullable();
      $table->json('branding_settings')->nullable();
      $table->json('email_settings')->nullable();
      $table->json('marketing_settings');
      $table->json('company_settings');
      $table->json('sender_settings');
      $table->json('subscription_settings')->nullable();
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
