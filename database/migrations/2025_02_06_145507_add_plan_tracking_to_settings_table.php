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
    Schema::table('settings', function (Blueprint $table) {
      $table->unsignedBigInteger('effective_plan_id')->nullable()->after('plan_id');
      $table->unsignedBigInteger('scheduled_plan_id')->nullable()->after('effective_plan_id');

      $table->foreign('effective_plan_id')->references('id')->on('plans');
      $table->foreign('scheduled_plan_id')->references('id')->on('plans');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('settings', function (Blueprint $table) {
      $table->dropForeign(['effective_plan_id']);
      $table->dropForeign(['scheduled_plan_id']);
      $table->dropColumn(['effective_plan_id', 'scheduled_plan_id']);
    });
  }
};
