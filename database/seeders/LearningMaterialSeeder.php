<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class LearningMaterialSeeder extends Seeder
{
  public function run(): void
  {
    $teachers = Teacher::all();
    $subjects = Subject::all();
    $classrooms = Classroom::all();

    $materials = [
      ['title' => 'Pengantar Aljabar Linear', 'type' => 'document', 'description' => 'Materi dasar aljabar linear: matriks, vektor, dan transformasi'],
      ['title' => 'Video Reaksi Kimia', 'type' => 'video', 'description' => 'Demonstrasi reaksi eksotermis dan endotermis', 'file_url' => 'https://youtube.com/watch?v=example1'],
      ['title' => 'Rangkuman Sejarah Indonesia', 'type' => 'text', 'content' => 'Proklamasi kemerdekaan Indonesia diproklamasikan pada tanggal 17 Agustus 1945 oleh Soekarno-Hatta.'],
      ['title' => 'Referensi Grammar Bahasa Inggris', 'type' => 'link', 'file_url' => 'https://www.grammarly.com/blog/grammar/'],
      ['title' => 'Podcast Biologi Sel', 'type' => 'audio', 'description' => 'Audio pembelajaran tentang struktur dan fungsi sel'],
      ['title' => 'Rumus Fisika Mekanika', 'type' => 'document', 'description' => 'Kumpulan rumus mekanika: gerak lurus, gerak parabola, gaya'],
      ['title' => 'Tutorial Python Dasar', 'type' => 'text', 'content' => 'Python adalah bahasa pemrograman tingkat tinggi. print("Hello World")'],
      ['title' => 'Statistika dan Probabilitas', 'type' => 'document', 'description' => 'Mean, median, modus, dan distribusi probabilitas'],
    ];

    foreach ($materials as $i => $m) {
      LearningMaterial::create([
        'title' => $m['title'],
        'type' => $m['type'],
        'description' => $m['description'] ?? null,
        'content' => $m['content'] ?? null,
        'file_url' => $m['file_url'] ?? null,
        'subject_id' => $subjects[$i % $subjects->count()]->id,
        'teacher_id' => $teachers[$i % $teachers->count()]->id,
        'classroom_id' => $i < 4 ? $classrooms[$i % $classrooms->count()]->id : null,
        'is_published' => true,
      ]);
    }
  }
}
