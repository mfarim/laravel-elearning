<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
  public function run(): void
  {
    $teachers = Teacher::all();
    $classrooms = [
      ['name' => 'X IPA 1', 'level' => 10, 'capacity' => 36, 'academic_year' => '2025/2026'],
      ['name' => 'X IPA 2', 'level' => 10, 'capacity' => 36, 'academic_year' => '2025/2026'],
      ['name' => 'X IPS 1', 'level' => 10, 'capacity' => 36, 'academic_year' => '2025/2026'],
      ['name' => 'XI IPA 1', 'level' => 11, 'capacity' => 34, 'academic_year' => '2025/2026'],
      ['name' => 'XI IPS 1', 'level' => 11, 'capacity' => 34, 'academic_year' => '2025/2026'],
      ['name' => 'XII IPA 1', 'level' => 12, 'capacity' => 32, 'academic_year' => '2025/2026'],
    ];

    foreach ($classrooms as $i => $c) {
      Classroom::create([
        ...$c,
        'homeroom_teacher_id' => $teachers[$i % $teachers->count()]->id,
      ]);
    }
  }
}
