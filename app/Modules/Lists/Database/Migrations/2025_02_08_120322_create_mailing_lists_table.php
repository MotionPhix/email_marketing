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
      $table->uuid('id')->primary();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('name');
      $table->text('description')->nullable();
      $table->boolean('is_default')->default(false);
      $table->json('metadata')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });

    Schema::create('list_recipients', function (Blueprint $table) {
      $table->uuid('list_id');
      $table->uuid('recipient_id');
      $table->string('status')->default('subscribed');
      $table->timestamp('subscribed_at')->nullable();
      $table->timestamp('unsubscribed_at')->nullable();
      $table->json('metadata')->nullable();
      $table->timestamps();

      $table->primary(['list_id', 'recipient_id']);

      $table->foreign('list_id')
        ->references('id')
        ->on('mailing_lists')
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
    Schema::dropIfExists('list_recipients');
    Schema::dropIfExists('mailing_lists');
  }
};
