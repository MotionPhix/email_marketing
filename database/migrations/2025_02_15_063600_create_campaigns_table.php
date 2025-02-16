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
      $table->string('name');
      $table->string('subject');
      $table->string('from_name');
      $table->string('from_email');
      $table->string('reply_to')->nullable();
      $table->longText('content');
      $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
      $table->string('status')->default('draft');
      $table->json('settings')->nullable();
      $table->json('recipients')->nullable();
      $table->timestamp('scheduled_at')->nullable();
      $table->timestamp('sent_at')->nullable();
      $table->timestamps();
      $table->softDeletes();
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
