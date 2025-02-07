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
      $table->uuid('uuid')->unique();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('name');
      $table->string('subject');
      $table->text('content');
      $table->json('template_data')->nullable();
      $table->string('from_name')->nullable();
      $table->string('from_email')->nullable();
      $table->string('reply_to')->nullable();
      $table->timestamp('scheduled_at')->nullable();
      $table->timestamp('started_at')->nullable();
      $table->timestamp('completed_at')->nullable();
      $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'failed'])->default('draft');
      $table->integer('total_recipients')->default(0);
      $table->integer('sent_count')->default(0);
      $table->integer('opened_count')->default(0);
      $table->integer('clicked_count')->default(0);
      $table->integer('bounced_count')->default(0);
      $table->integer('complained_count')->default(0);
      $table->timestamps();
      $table->softDeletes();
    });

    Schema::create('campaign_lists', function (Blueprint $table) {
      $table->id();
      $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
      $table->foreignId('list_id')->constrained()->cascadeOnDelete();
      $table->timestamps();

      $table->unique(['campaign_id', 'list_id']);
    });

    Schema::create('campaign_events', function (Blueprint $table) {
      $table->id();
      $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
      $table->string('email');
      $table->enum('type', ['sent', 'opened', 'clicked', 'bounced', 'complained']);
      $table->json('metadata')->nullable();
      $table->timestamps();

      $table->index(['campaign_id', 'type']);
      $table->index('email');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('campaign_events');
    Schema::dropIfExists('campaign_lists');
    Schema::dropIfExists('campaigns');
  }
};
