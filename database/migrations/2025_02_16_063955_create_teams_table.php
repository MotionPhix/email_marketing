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
    Schema::create('teams', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->boolean('personal_team');
      $table->json('settings')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->foreignId('owner_id')
        ->references('id')
        ->on('users')
        ->onDelete('cascade');
    });

    Schema::create('team_user', function (Blueprint $table) {
      $table->id();
      $table->string('role');
      $table->timestamps();

      $table->foreignId('team_id')
        ->references('id')
        ->on('teams')
        ->onDelete('cascade');

      $table->foreignId('user_id')
        ->references('id')
        ->on('users')
        ->onDelete('cascade');

      $table->unique(['team_id', 'user_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
  }
};
