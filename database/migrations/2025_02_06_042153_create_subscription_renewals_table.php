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
    Schema::create('subscription_renewals', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->index();
      $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
      $table->string('paychangu_reference');
      $table->integer('amount');
      $table->string('status');
      $table->timestamp('completed_at')->nullable();
      $table->timestamp('failed_at')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('subscription_renewals');
  }
};
