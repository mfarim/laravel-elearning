<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
  public function run(): void
  {
    $classrooms = Classroom::all();
    $firstNames = [
      'Andi',
      'Rina',
      'Dimas',
      'Putri',
      'Fajar',
      'Maya',
      'Rizki',
      'Sari',
      'Yoga',
      'Nisa',
      'Bayu',
      'Lina',
      'Galih',
      'Wulan',
      'Arif',
      'Intan',
      'Hendra',
      'Dewi',
      'Fandi',
      'Ayu'
    ];
    $lastNames = ['Pratama', 'Sari', 'Wijaya', 'Kusuma', 'Hidayat', 'Ramadhani', 'Nugroho', 'Permata', 'Saputra', 'Fitriani'];

    $counter = 1;
    foreach ($classrooms as $classroom) {
      $count = min(5, $classroom->capacity); // 5 students per class for demo
      for ($i = 0; $i < $count; $i++) {
        $fn = $firstNames[($counter - 1) % count($firstNames)];
        $ln = $lastNames[($counter - 1) % count($lastNames)];
        $name = "{$fn} {$ln}";
        $email = strtolower("{$fn}.{$ln}") . $counter . '@siswa.id';

        $user = User::create([
          'name' => $name,
          'email' => $email,
          'password' => Hash::make('password'),
          'is_active' => true,
        ]);
        $user->assignRole('siswa');
        Student::create([
          'user_id' => $user->id,
          'classroom_id' => $classroom->id,
          'nis' => '2025' . str_pad($counter, 4, '0', STR_PAD_LEFT),
          'nisn' => '00' . str_pad($counter, 8, '0', STR_PAD_LEFT),
          'gender' => $i % 2 === 0 ? 'L' : 'P',
          'birth_date' => now()->subYears(rand(15, 17))->subDays(rand(0, 365)),
          'address' => 'Jl. Pelajar No. ' . $counter,
        ]);
        $counter++;
      }
    }
  }
}
