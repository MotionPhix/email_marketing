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
      $table->uuid('uuid')->index();
      $table->string('title');
      $table->string('subject')->index();
      $table->text('description')->nullable();
      $table->enum('status', ['draft', 'scheduled', 'sent'])->default('draft');
      $table->timestamp('scheduled_at')->nullable(); // Scheduling date
      $table->timestamp('sent_at')->nullable();
      $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Campaign creator
      $table->foreignId('audience_id')->nullable()->constrained()->cascadeOnDelete();
      $table->foreignId('template_id')->nullable()->constrained()->onDelete('set null');
      $table->timestamps();

      // Add indexes
      $table->index(['status', 'scheduled_at']);
      $table->index('created_at');
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
