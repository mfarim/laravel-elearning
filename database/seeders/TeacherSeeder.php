<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
  public function run(): void
  {
    $teachers = [
      ['name' => 'Budi Santoso', 'email' => 'budi@sekolah.id', 'nip' => '198501012010011001', 'phone' => '081234567001'],
      ['name' => 'Siti Rahayu', 'email' => 'siti@sekolah.id', 'nip' => '198602022011012002', 'phone' => '081234567002'],
      ['name' => 'Ahmad Hidayat', 'email' => 'ahmad@sekolah.id', 'nip' => '198703032012013003', 'phone' => '081234567003'],
      ['name' => 'Dewi Lestari', 'email' => 'dewi@sekolah.id', 'nip' => '198804042013014004', 'phone' => '081234567004'],
      ['name' => 'Eko Prasetyo', 'email' => 'eko@sekolah.id', 'nip' => '198905052014015005', 'phone' => '081234567005'],
    ];

    foreach ($teachers as $t) {
      $user = User::create([
        'name' => $t['name'],
        'email' => $t['email'],
        'password' => Hash::make('password'),
        'phone' => $t['phone'],
        'is_active' => true,
      ]);
      $user->assignRole('guru');
      Teacher::create([
        'user_id' => $user->id,
        'nip' => $t['nip'],
        'address' => 'Jl. Pendidikan No. ' . rand(1, 100) . ', Kota Pendidikan',
      ]);
    }
  }
}
