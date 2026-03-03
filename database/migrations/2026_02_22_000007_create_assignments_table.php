<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('assignments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
      $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
      $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
      $table->string('title');
      $table->text('description')->nullable();
      $table->text('instructions')->nullable();
      $table->string('attachment')->nullable();
      $table->integer('max_score')->default(100);
      $table->dateTime('due_date');
      $table->boolean('allow_late_submission')->default(false);
      $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('assignments');
  }
};
