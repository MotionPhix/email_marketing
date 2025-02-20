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
    Schema::create('mailing_lists', function (Blueprint $table) {
      $table->id();
      $table->foreignId('team_id')->constrained()->cascadeOnDelete();
      $table->string('name');
      $table->string('description')->nullable();
      $table->json('settings')->nullable();
      $table->boolean('is_default')->default(false);
      $table->string('status')->default('active');
      $table->timestamps();
      $table->softDeletes();

      $table->unique(['team_id', 'name']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('mailing_lists');
  }
};
