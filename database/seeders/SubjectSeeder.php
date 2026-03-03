<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
  public function run(): void
  {
    $teachers = Teacher::all();
    $subjects = [
      ['code' => 'MTK', 'name' => 'Matematika', 'credits' => 4, 'description' => 'Aljabar, geometri, statistika, kalkulus'],
      ['code' => 'BIN', 'name' => 'Bahasa Indonesia', 'credits' => 4, 'description' => 'Tata bahasa, sastra, menulis'],
      ['code' => 'BIG', 'name' => 'Bahasa Inggris', 'credits' => 3, 'description' => 'Grammar, reading, writing, speaking'],
      ['code' => 'FIS', 'name' => 'Fisika', 'credits' => 3, 'description' => 'Mekanika, termodinamika, gelombang, optik'],
      ['code' => 'KIM', 'name' => 'Kimia', 'credits' => 3, 'description' => 'Struktur atom, reaksi kimia, larutan'],
      ['code' => 'BIO', 'name' => 'Biologi', 'credits' => 3, 'description' => 'Sel, genetika, ekologi, evolusi'],
      ['code' => 'SEJ', 'name' => 'Sejarah', 'credits' => 2, 'description' => 'Sejarah Indonesia dan dunia'],
      ['code' => 'INF', 'name' => 'Informatika', 'credits' => 3, 'description' => 'Pemrograman, jaringan, basis data'],
    ];

    foreach ($subjects as $i => $s) {
      Subject::create([
        ...$s,
        'teacher_id' => $teachers[$i % $teachers->count()]->id,
      ]);
    }
  }
}
