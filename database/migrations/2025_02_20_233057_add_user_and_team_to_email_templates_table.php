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

    Schema::table('email_templates', function (Blueprint $table) {
      $table->foreignId('user_id')->index()
        ->after('id')
        ->constrained()
        ->cascadeOnDelete();

      $table->foreignId('team_id')->index()
        ->after('user_id')
        ->constrained()
        ->cascadeOnDelete();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('email_templates', function (Blueprint $table) {

      $table->dropForeign(['team_id']);
      $table->dropForeign(['user_id']);
      $table->dropColumn('team_id');
      $table->dropColumn('user_id');

    });
  }
};
