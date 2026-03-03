<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_attempts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('examination_id')->constrained()->cascadeOnDelete();
      $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
      $table->dateTime('started_at');
      $table->dateTime('finished_at')->nullable();
      $table->integer('score')->nullable();
      $table->boolean('is_passed')->default(false);
      $table->boolean('is_force_finished')->default(false);
      $table->integer('attempt_number')->default(1);
      $table->enum('status', ['in_progress', 'completed', 'force_finished'])->default('in_progress');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_attempts');
  }
};
