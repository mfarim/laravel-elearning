<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('examinations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
      $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
      $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
      $table->string('title');
      $table->text('description')->nullable();
      $table->enum('type', ['quiz', 'uts', 'uas', 'praktik', 'tryout'])->default('quiz');
      $table->integer('duration_minutes')->default(60);
      $table->integer('passing_score')->default(75);
      $table->dateTime('start_at');
      $table->dateTime('end_at');
      $table->date('exam_date')->nullable();
      $table->integer('total_questions')->nullable();
      $table->boolean('shuffle_questions')->default(false);
      $table->boolean('shuffle_options')->default(false);
      $table->boolean('show_result')->default(false);
      $table->string('exam_package')->nullable();
      $table->boolean('allow_retry')->default(false);
      $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('examinations');
  }
};
