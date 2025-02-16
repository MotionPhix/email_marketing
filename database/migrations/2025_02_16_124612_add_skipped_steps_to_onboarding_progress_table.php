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
    Schema::table('onboarding_progress', function (Blueprint $table) {
      $table->json('skipped_steps')->after('completed_steps')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('onboarding_progress', function (Blueprint $table) {
      $table->dropColumn('skipped_steps');
    });
  }
};
