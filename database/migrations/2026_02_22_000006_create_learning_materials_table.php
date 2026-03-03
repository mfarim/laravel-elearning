<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('learning_materials', function (Blueprint $table) {
      $table->id();
      $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
      $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
      $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
      $table->string('title');
      $table->text('description')->nullable();
      $table->enum('type', ['document', 'video', 'text', 'link', 'audio']);
      $table->string('file_path')->nullable();
      $table->string('file_url')->nullable();
      $table->longText('content')->nullable();
      $table->boolean('is_published')->default(false);
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('learning_materials');
  }
};
