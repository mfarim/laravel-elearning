<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('material_views', function (Blueprint $table) {
      $table->id();
      $table->foreignId('learning_material_id')->constrained()->cascadeOnDelete();
      $table->foreignId('student_id')->constrained()->cascadeOnDelete();
      $table->timestamp('viewed_at');
      $table->timestamps();
      $table->unique(['learning_material_id', 'student_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('material_views');
  }
};
