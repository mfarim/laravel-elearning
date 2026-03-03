<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
  public function run(): void
  {
    $teachers = Teacher::all();
    $subjects = Subject::all();
    $classrooms = Classroom::all();

    $assignments = [
      ['title' => 'Latihan Soal Aljabar', 'description' => 'Kerjakan soal aljabar halaman 45-50', 'max_score' => 100, 'status' => 'published'],
      ['title' => 'Essay Bahasa Indonesia', 'description' => 'Tulis essay 500 kata tentang budaya Indonesia', 'max_score' => 100, 'status' => 'published'],
      ['title' => 'Laporan Praktikum Kimia', 'description' => 'Buat laporan praktikum reaksi redoks', 'max_score' => 80, 'status' => 'published'],
      ['title' => 'Reading Comprehension', 'description' => 'Read the passage and answer questions', 'max_score' => 50, 'status' => 'published'],
      ['title' => 'Tugas Fisika: Gaya', 'description' => 'Selesaikan soal gaya dan gerak', 'max_score' => 100, 'status' => 'draft'],
      ['title' => 'Project Informatika', 'description' => 'Buat program kalkulator sederhana', 'max_score' => 100, 'status' => 'published'],
    ];

    foreach ($assignments as $i => $a) {
      Assignment::create([
        ...$a,
        'subject_id' => $subjects[$i % $subjects->count()]->id,
        'teacher_id' => $teachers[$i % $teachers->count()]->id,
        'classroom_id' => $classrooms[$i % $classrooms->count()]->id,
        'due_date' => now()->addDays(rand(3, 14)),
        'allow_late_submission' => $i % 3 === 0,
      ]);
    }
  }
}
