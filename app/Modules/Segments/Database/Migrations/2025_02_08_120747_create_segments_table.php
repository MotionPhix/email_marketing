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
    Schema::create('segments', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('name');
      $table->text('description')->nullable();
      $table->json('conditions');
      $table->enum('match_type', ['all', 'any'])->default('all');
      $table->timestamp('last_applied_at')->nullable();
      $table->json('metadata')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });

    Schema::create('segment_recipients', function (Blueprint $table) {
      $table->uuid('segment_id');
      $table->uuid('recipient_id');
      $table->timestamp('added_at');
      $table->timestamps();

      $table->primary(['segment_id', 'recipient_id']);

      $table->foreign('segment_id')
        ->references('id')
        ->on('segments')
        ->cascadeOnDelete();

      $table->foreign('recipient_id')
        ->references('id')
        ->on('recipients')
        ->cascadeOnDelete();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('segments');
  }
};
