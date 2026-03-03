<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('assignment_submissions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
      $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
      $table->string('file_path')->nullable();
      $table->text('notes')->nullable();
      $table->integer('score')->nullable();
      $table->text('feedback')->nullable();
      $table->enum('status', ['submitted', 'graded', 'late'])->default('submitted');
      $table->dateTime('submitted_at');
      $table->dateTime('graded_at')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('assignment_submissions');
  }
};
