<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('questions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('examination_id')->constrained()->cascadeOnDelete();
      $table->longText('question_text');
      $table->enum('question_type', ['multiple_choice', 'short_answer', 'essay'])->default('multiple_choice');
      $table->json('options')->nullable();
      $table->text('correct_answer')->nullable();
      $table->string('audio_path')->nullable();
      $table->string('image_path')->nullable();
      $table->text('explanation')->nullable();
      $table->integer('points')->default(1);
      $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('questions');
  }
};
