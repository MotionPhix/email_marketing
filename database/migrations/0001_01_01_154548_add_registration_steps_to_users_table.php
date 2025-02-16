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
    Schema::table('users', function (Blueprint $table) {
      $table->string('registration_status')->default('incomplete');
      $table->json('completed_registration_steps')->nullable();
      $table->timestamp('trial_ends_at')->nullable();
      $table->string('company_name')->nullable();
      $table->string('industry')->nullable();
      $table->string('company_size')->nullable();
      $table->string('website')->nullable();
      $table->string('phone')->nullable();
      $table->string('role')->nullable();
      $table->json('marketing_preferences')->nullable();
      $table->timestamp('registration_completed_at')->nullable();

      // Email configuration
      $table->string('default_sender_name')->nullable();
      $table->string('default_sender_email')->nullable();
      $table->boolean('email_verified')->default(false);
      $table->string('verification_token')->nullable();

      // Indexes
      $table->index('registration_status');
      $table->index('industry');
    });

    Schema::create('registration_data', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->integer('step');
      $table->json('data');
      $table->json('validation_errors')->nullable();
      $table->timestamp('completed_at')->nullable();
      $table->timestamps();

      $table->index(['user_id', 'step']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn([
        'registration_status',
        'completed_registration_steps',
        'company_name',
        'industry',
        'company_size',
        'website',
        'phone',
        'role',
        'marketing_preferences',
        'registration_completed_at',
        'default_sender_name',
        'default_sender_email',
        'email_verified',
        'verification_token'
      ]);
    });

    Schema::dropIfExists('registration_data');
  }
};
