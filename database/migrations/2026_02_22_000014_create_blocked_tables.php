<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('blocked_ips', function (Blueprint $table) {
      $table->id();
      $table->string('ip_address')->unique();
      $table->string('reason');
      $table->text('description')->nullable();
      $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
      $table->dateTime('blocked_at');
      $table->dateTime('expires_at')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
      $table->softDeletes();
    });

    Schema::create('blocked_users', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('reason');
      $table->text('description')->nullable();
      $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
      $table->dateTime('blocked_at');
      $table->dateTime('expires_at')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('blocked_users');
    Schema::dropIfExists('blocked_ips');
  }
};
