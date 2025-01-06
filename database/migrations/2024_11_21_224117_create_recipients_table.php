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
    Schema::create('recipients', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid');

      $table->string('name')->nullable();
      $table->string('email')->unique();

      $table->string('status')->default('active'); // could be banned, unsubscribed (from everything)

      $table->enum(
        'gender', ['male', 'female', 'unspecified']
      )->default('unspecified');

      $table->foreignId('user_id')->constrained()->cascadeOnDelete();

      $table->timestamps();

      $table->softDeletes();

      // Add indexes for better performance
      $table->index(['email', 'status']);
      $table->index('created_at');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('recipients');
  }
};
