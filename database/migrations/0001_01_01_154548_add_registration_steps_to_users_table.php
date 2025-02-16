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
      $table->timestamp('registration_completed_at')->nullable();

      // Indexes
      $table->index('registration_status');
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
        'registration_completed_at',
      ]);
    });
  }
};
