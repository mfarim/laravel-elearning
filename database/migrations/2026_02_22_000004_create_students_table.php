<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('students', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
      $table->string('nis')->unique();
      $table->string('nisn')->nullable();
      $table->date('birth_date')->nullable();
      $table->enum('gender', ['L', 'P']);
      $table->text('address')->nullable();
      $table->string('photo')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('students');
  }
};
