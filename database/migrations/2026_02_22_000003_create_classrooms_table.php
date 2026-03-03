<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('classrooms', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->integer('level');
      $table->foreignId('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
      $table->integer('capacity')->default(30);
      $table->string('academic_year');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('classrooms');
  }
};
