<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_answers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
      $table->foreignId('question_id')->constrained()->cascadeOnDelete();
      $table->text('answer_text')->nullable();
      $table->boolean('is_correct')->nullable();
      $table->integer('points_earned')->default(0);
      $table->dateTime('answered_at')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_answers');
  }
};
