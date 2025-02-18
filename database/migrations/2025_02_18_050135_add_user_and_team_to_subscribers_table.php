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
    Schema::table('subscribers', function (Blueprint $table) {
      $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
      $table->foreignId('team_id')->after('user_id')->constrained()->onDelete('cascade');

      // Drop the old unique index
      $table->dropUnique(['email']);
      // Add new composite unique index
      $table->unique(['email', 'team_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('subscribers', function (Blueprint $table) {
      // Restore the old unique index
      $table->unique(['email']);
      // Drop the new composite unique index
      $table->dropUnique(['email', 'team_id']);

      $table->dropForeign(['user_id']);
      $table->dropForeign(['team_id']);
      $table->dropColumn(['user_id', 'team_id']);
    });
  }
};
