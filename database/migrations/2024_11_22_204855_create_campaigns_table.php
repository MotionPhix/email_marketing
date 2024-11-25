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
    Schema::create('campaigns', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid');
      $table->string('title');
      $table->string('subject');
      $table->text('description')->nullable();
      $table->enum('status', ['draft', 'scheduled', 'sent'])->default('draft');
      $table->timestamp('scheduled_at')->nullable(); // Scheduling date
      $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Campaign creator
      $table->foreignId('audience_id')->nullable()->constrained()->cascadeOnDelete();
      $table->foreignId('template_id')->nullable()->constrained('templates')->onDelete('set null');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('campaigns');
  }
};
