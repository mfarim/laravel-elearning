<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('assignment_discussions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->text('message');
      $table->foreignId('parent_id')->nullable()->constrained('assignment_discussions')->cascadeOnDelete();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('assignment_discussions');
  }
};
