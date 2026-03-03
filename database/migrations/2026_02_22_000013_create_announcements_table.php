<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('announcements', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->longText('content');
      $table->enum('target', ['all', 'teacher', 'student'])->default('all');
      $table->boolean('is_published')->default(false);
      $table->dateTime('published_at')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('announcements');
  }
};
